<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    $columns = [];
    if ($driver === 'mysql') {
        foreach ($pdo->query('SHOW COLUMNS FROM users')->fetchAll() as $column) {
            $columns[] = $column['Field'];
        }
    } else {
        foreach ($pdo->query('PRAGMA table_info(users)')->fetchAll() as $column) {
            $columns[] = $column['name'];
        }
    }

    $string = $driver === 'mysql' ? 'VARCHAR(191)' : 'TEXT';
    $text = 'TEXT';
    $datetime = $driver === 'mysql' ? 'DATETIME NULL' : 'TEXT NULL';

    if (!in_array('telegram_link_code', $columns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN telegram_link_code {$string} NULL");
    }
    if (!in_array('telegram_link_expires_at', $columns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN telegram_link_expires_at {$datetime}");
    }
    if (!in_array('web_push_subscription', $columns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN web_push_subscription {$text} NULL");
    }
};
