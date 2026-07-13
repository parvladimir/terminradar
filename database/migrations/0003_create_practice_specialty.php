<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec('CREATE TABLE IF NOT EXISTS practice_specialty (practice_id INTEGER NOT NULL, specialty_id INTEGER NOT NULL, PRIMARY KEY (practice_id, specialty_id))');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_practice_specialty_specialty ON practice_specialty(specialty_id)');
};
