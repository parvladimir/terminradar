<?php

declare(strict_types=1);

namespace TerminRadar\Core;

use PDO;

final class Database
{
    private ?PDO $pdo = null;

    public function __construct(private readonly Config $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $driver = (string) $this->config->get('database.default', 'sqlite');
        $connection = $this->config->get('database.connections.' . $driver, []);

        if ($driver === 'sqlite') {
            $database = (string) $connection['database'];
            $directory = dirname($database);
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
            $this->pdo = new PDO('sqlite:' . $database);
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $connection['host'],
                $connection['port'],
                $connection['database'],
                $connection['charset'] ?? 'utf8mb4'
            );
            $this->pdo = new PDO($dsn, (string) $connection['username'], (string) $connection['password']);
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $this->pdo;
    }
}
