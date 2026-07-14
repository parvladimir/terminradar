<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    $columns = [];
    if ($driver === 'mysql') {
        foreach ($pdo->query('SHOW COLUMNS FROM watches')->fetchAll() as $column) {
            $columns[] = $column['Field'];
        }
    } else {
        foreach ($pdo->query('PRAGMA table_info(watches)')->fetchAll() as $column) {
            $columns[] = $column['name'];
        }
    }

    $datetime = $driver === 'mysql' ? 'DATETIME NULL' : 'TEXT NULL';
    if (!in_array('current_best_slot_at', $columns, true)) {
        $pdo->exec("ALTER TABLE watches ADD COLUMN current_best_slot_at {$datetime}");
    }
};
