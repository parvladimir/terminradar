<?php

declare(strict_types=1);

namespace TerminRadar\Core;

use TerminRadar\Controllers\Controller;
use TerminRadar\Database\MigrationRunner;
use TerminRadar\Database\SeederRunner;

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
            'schedule:run' => $this->line('Scheduler ready: due source selection will run in stage 3.'),
            'appointments:check' => $this->line('Appointment source checks are wired in stage 3.'),
            'appointments:check-source' => $this->line('Usage accepted: appointments:check-source {id}. Provider implementation follows in stage 3.'),
            'appointments:discover-types' => $this->line('Usage accepted: appointments:discover-types {sourceId}. Provider implementation follows in stage 3.'),
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
}
