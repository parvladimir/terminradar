<?php

declare(strict_types=1);

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

$auth = new TerminRadar\Services\AuthService(new TerminRadar\Repositories\UserRepository($pdo));
$user = $auth->register(['name' => 'Test User', 'email' => 'test@example.de', 'password' => 'VerySecret123!'], 'uk');
ok($user !== null, 'user registration creates account');
ok($auth->attempt('test@example.de', 'VerySecret123!') !== null, 'login verifies password');
ok($auth->attempt('test@example.de', 'wrong-password') === null, 'login rejects bad password');

echo "All tests passed.\n";
