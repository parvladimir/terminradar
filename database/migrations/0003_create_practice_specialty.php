<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    $pdo->exec('CREATE TABLE IF NOT EXISTS practice_specialty (practice_id INTEGER NOT NULL, specialty_id INTEGER NOT NULL, PRIMARY KEY (practice_id, specialty_id))');
    if ($driver === 'mysql') {
        $stmt = $pdo->prepare('SHOW INDEX FROM practice_specialty WHERE Key_name = :name');
        $stmt->execute(['name' => 'idx_practice_specialty_specialty']);
        if (!$stmt->fetch()) {
            $pdo->exec('CREATE INDEX idx_practice_specialty_specialty ON practice_specialty(specialty_id)');
        }
    } else {
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_practice_specialty_specialty ON practice_specialty(specialty_id)');
    }
};
