<?php

declare(strict_types=1);

namespace TerminRadar\Core;

use TerminRadar\Controllers\Controller;
use TerminRadar\Database\MigrationRunner;
use TerminRadar\Database\SeederRunner;
use TerminRadar\Providers\ProviderRegistry;
use TerminRadar\Repositories\AppointmentSourceRepository;
use TerminRadar\Services\AppointmentCheckService;

final class Application
{
    public readonly Config $config;
    public readonly Database $database;
    public readonly Session $session;
    public readonly Translator $translator;
    public readonly View $view;
    public readonly Router $router;

    public function __construct(public readonly string $basePath)
    {
        $this->config = new Config($basePath);
        $this->database = new Database($this->config);
        $this->session = new Session($basePath . '/storage/sessions');
        $this->translator = new Translator($basePath, $this->config, $this->session);
        $this->view = new View($basePath . '/resources/views', $this->translator, $this->session);
        $this->router = new Router($this);
        (require $basePath . '/config/routes.php')($this->router);
    }

    public function handleWeb(): void
    {
        $this->session->start();
        $response = $this->router->dispatch(Request::capture());
        $response->send();
    }

    /** @param list<string> $argv */
    public function handleConsole(array $argv): void
    {
        $command = $argv[1] ?? 'help';
        $exitCode = match ($command) {
            'migrate' => (new MigrationRunner($this->database->pdo(), $this->basePath))->run(),
            'db:seed' => (new SeederRunner($this->database->pdo(), $this->basePath))->run(),
            'migrate:fresh' => (new MigrationRunner($this->database->pdo(), $this->basePath))->fresh(),
            'schedule:run' => $this->checkDueSources(),
            'appointments:check' => $this->checkDueSources(),
            'appointments:check-source' => $this->checkSource((int) ($argv[2] ?? 0)),
            'appointments:discover-types' => $this->discoverTypes((int) ($argv[2] ?? 0)),
            'appointments:test-notifications' => $this->line('Usage accepted: appointments:test-notifications {userId}. Notification transports follow in stage 4.'),
            'appointments:cleanup' => $this->line('Cleanup command ready; retention policy follows in stage 3.'),
            default => $this->help(),
        };

        exit($exitCode);
    }

    public function controller(string $class): Controller
    {
        return new $class($this);
    }

    private function line(string $message): int
    {
        echo $message . PHP_EOL;
        return 0;
    }

    private function help(): int
    {
        echo "TerminRadar console\n";
        echo "Commands: migrate, migrate:fresh, db:seed, schedule:run, appointments:check, appointments:check-source, appointments:discover-types, appointments:test-notifications, appointments:cleanup\n";
        return 0;
    }

    private function checkDueSources(): int
    {
        $result = (new AppointmentCheckService($this->database->pdo()))->checkDue();
        echo json_encode($result, JSON_THROW_ON_ERROR) . PHP_EOL;
        return $result['errors'] > 0 ? 1 : 0;
    }

    private function checkSource(int $sourceId): int
    {
        if ($sourceId <= 0) {
            echo "Usage: appointments:check-source {id}\n";
            return 1;
        }

        $result = (new AppointmentCheckService($this->database->pdo()))->checkSource($sourceId);
        echo json_encode($result, JSON_THROW_ON_ERROR) . PHP_EOL;
        return $result['errors'] > 0 ? 1 : 0;
    }

    private function discoverTypes(int $sourceId): int
    {
        if ($sourceId <= 0) {
            echo "Usage: appointments:discover-types {sourceId}\n";
            return 1;
        }

        $source = (new AppointmentSourceRepository($this->database->pdo()))->find($sourceId);
        if ($source === null) {
            echo "Source not found.\n";
            return 1;
        }

        $adapter = (new ProviderRegistry())->forSource($source);
        echo json_encode(['data' => $adapter->fetchAppointmentTypes($source)], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        return 0;
    }
}
