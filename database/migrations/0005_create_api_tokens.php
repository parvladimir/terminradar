<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    $auto = $driver === 'mysql' ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $string = $driver === 'mysql' ? 'VARCHAR(191)' : 'TEXT';
    $datetime = $driver === 'mysql' ? 'DATETIME NULL' : 'TEXT NULL';
    $notNullDate = $driver === 'mysql' ? 'DATETIME NOT NULL' : 'TEXT NOT NULL';

    $pdo->exec("CREATE TABLE IF NOT EXISTS api_tokens (
        id {$auto}, user_id INTEGER NOT NULL, name {$string} NOT NULL, token_hash {$string} NOT NULL UNIQUE,
        last_used_at {$datetime}, expires_at {$datetime}, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    if ($driver === 'mysql') {
        $stmt = $pdo->prepare('SHOW INDEX FROM api_tokens WHERE Key_name = :name');
        $stmt->execute(['name' => 'idx_api_tokens_user_id']);
        if (!$stmt->fetch()) {
            $pdo->exec('CREATE INDEX idx_api_tokens_user_id ON api_tokens(user_id)');
        }
    } else {
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_api_tokens_user_id ON api_tokens(user_id)');
    }
};
