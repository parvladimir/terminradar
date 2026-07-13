<?php

declare(strict_types=1);

namespace TerminRadar\Database;

use PDO;

final class SeederRunner
{
    public function __construct(private readonly PDO $pdo, private readonly string $basePath)
    {
    }

    public function run(): int
    {
        foreach (glob($this->basePath . '/database/seeders/*.php') ?: [] as $file) {
            (require $file)($this->pdo);
            echo 'Seeded ' . basename($file) . PHP_EOL;
        }
        return 0;
    }
}
