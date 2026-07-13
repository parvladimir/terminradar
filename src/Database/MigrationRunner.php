<?php

declare(strict_types=1);

namespace TerminRadar\Database;

use PDO;

final class MigrationRunner
{
    public function __construct(private readonly PDO $pdo, private readonly string $basePath)
    {
    }

    public function run(): int
    {
        $this->ensureMigrationsTable();
        $ran = $this->ranMigrations();
        foreach (glob($this->basePath . '/database/migrations/*.php') ?: [] as $file) {
            $name = basename($file);
            if (in_array($name, $ran, true)) {
                continue;
            }
            $migration = require $file;
            $migration($this->pdo, $this->driver());
            $stmt = $this->pdo->prepare('INSERT INTO migrations (migration, ran_at) VALUES (:migration, :ran_at)');
            $stmt->execute(['migration' => $name, 'ran_at' => date('c')]);
            echo "Migrated {$name}\n";
        }
        return 0;
    }

    public function fresh(): int
    {
        foreach ([
            'source_locks', 'provider_logs', 'notifications', 'watch_matches', 'watches', 'users', 'appointment_slots',
            'appointment_sources', 'appointment_types', 'doctor_specialty', 'doctors', 'practices', 'cities',
            'federal_states', 'medical_specialties', 'migrations',
        ] as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS {$table}");
        }
        return $this->run();
    }

    private function ensureMigrationsTable(): void
    {
        $auto = $this->driver() === 'mysql' ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $string = $this->driver() === 'mysql' ? 'VARCHAR(255)' : 'TEXT';
        $datetime = $this->driver() === 'mysql' ? 'DATETIME NOT NULL' : 'TEXT NOT NULL';
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (id {$auto}, migration {$string} NOT NULL UNIQUE, ran_at {$datetime})");
    }

    /** @return list<string> */
    private function ranMigrations(): array
    {
        $stmt = $this->pdo->query('SELECT migration FROM migrations');
        return array_column($stmt->fetchAll(), 'migration');
    }

    private function driver(): string
    {
        return (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
