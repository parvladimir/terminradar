<?php

declare(strict_types=1);

$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = 'storage/testing.sqlite';

$app = require dirname(__DIR__) . '/bootstrap/app.php';

function ok(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "OK: {$message}\n";
}

(new TerminRadar\Database\MigrationRunner($app->database->pdo(), $app->basePath))->fresh();
(new TerminRadar\Database\SeederRunner($app->database->pdo(), $app->basePath))->run();

$pdo = $app->database->pdo();

$count = (int) $pdo->query('SELECT COUNT(*) FROM medical_specialties')->fetchColumn();
ok($count >= 18, 'medical specialties are seeded');

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
ok((bool) $admin, 'admin user is seeded');
ok(password_verify(env('ADMIN_PASSWORD', 'ChangeMeImmediately123!'), $admin['password_hash']), 'admin password is hashed and verifiable');

$source = $pdo->query("SELECT * FROM appointment_sources WHERE provider = 'docvisit' LIMIT 1")->fetch();
ok((bool) $source, 'DocVisit appointment source is seeded');

$repo = new TerminRadar\Repositories\SpecialtyRepository($pdo);
ok($repo->active('uk')[0]['name'] !== '', 'localized specialty repository works');
ok($app->translator->get('home.title') !== 'home.title', 'app translation fallback works');

$practiceRepo = new TerminRadar\Repositories\PracticeRepository($pdo);
$marlPractices = $practiceRepo->search(['city' => 'Marl', 'specialty' => 'urologie'], 'de');
ok(count($marlPractices) >= 1, 'practice catalog finds seeded Urologie Marl source');
$practice = $practiceRepo->find((int) $marlPractices[0]['id'], 'de');
ok($practice !== null && count($practice['sources']) >= 1, 'practice detail loads sources');

$fixture = file_get_contents(__DIR__ . '/fixtures/docvisit_slots.html');
$adapter = new TerminRadar\Providers\DocVisitProviderAdapter();
$rawSlots = $adapter->fetchAvailableSlots([
    'id' => 999,
    'provider' => 'docvisit',
    'source_url' => 'https://example.invalid/docvisit',
    'booking_url' => 'https://example.invalid/book',
    'fixture_html' => $fixture,
]);
ok(count($rawSlots) === 2, 'DocVisit adapter parses fixture slots');
$normalizedSlots = array_map(static fn (array $raw) => $adapter->normalizeSlot($raw), $rawSlots);
$slotRepo = new TerminRadar\Repositories\AppointmentSlotRepository($pdo);
$sourceId = (int) $pdo->query("SELECT id FROM appointment_sources WHERE provider = 'docvisit' LIMIT 1")->fetchColumn();
$sync = $slotRepo->sync($sourceId, $normalizedSlots);
ok($sync['new'] === 2, 'slot sync stores new slots');
$sync = $slotRepo->sync($sourceId, []);
ok($sync['disappeared'] === 0, 'first empty response does not mark slots disappeared');
$sync = $slotRepo->sync($sourceId, []);
ok($sync['disappeared'] === 2, 'second confirmed empty response marks disappeared slots');

$auth = new TerminRadar\Services\AuthService(new TerminRadar\Repositories\UserRepository($pdo));
$user = $auth->register(['name' => 'Test User', 'email' => 'test@example.de', 'password' => 'VerySecret123!'], 'uk');
ok($user !== null, 'user registration creates account');
ok($auth->attempt('test@example.de', 'VerySecret123!') !== null, 'login verifies password');
ok($auth->attempt('test@example.de', 'wrong-password') === null, 'login rejects bad password');

$slotRepo->sync($sourceId, $normalizedSlots);
$watchId = (new TerminRadar\Repositories\WatchRepository($pdo))->create((int) $user['id'], [
    'name' => 'Test watch',
    'practice_id' => 1,
    'earliest_date' => '2026-07-01',
    'latest_date' => '2026-12-31',
    'time_from' => '08:00',
    'time_to' => '16:00',
    'frequency_minutes' => 15,
    'notification_email' => '1',
]);
ok($watchId > 0, 'watch repository creates watch');
$matched = (new TerminRadar\Services\WatchMatchingService($pdo))->matchSource($sourceId);
ok($matched >= 1, 'watch matching creates matches');
$notifications = (int) $pdo->query('SELECT COUNT(*) FROM notifications')->fetchColumn();
ok($notifications >= 1, 'watch matching creates notification records');
$matchedAgain = (new TerminRadar\Services\WatchMatchingService($pdo))->matchSource($sourceId);
ok($matchedAgain === 0, 'watch matching does not duplicate existing matches');

$tokenRepo = new TerminRadar\Repositories\ApiTokenRepository($pdo);
$token = $tokenRepo->create((int) $user['id'], 'test');
ok(strlen($token) === 64, 'api token repository creates plain token once');
ok($tokenRepo->userForToken($token)['email'] === 'test@example.de', 'api token authenticates user');
$tokenRepo->revoke($token);
ok($tokenRepo->userForToken($token) === null, 'api token revoke works');

echo "All tests passed.\n";
