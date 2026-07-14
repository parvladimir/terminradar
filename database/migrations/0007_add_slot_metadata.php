<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    $columns = [];
    if ($driver === 'mysql') {
        foreach ($pdo->query('SHOW COLUMNS FROM appointment_slots')->fetchAll() as $column) {
            $columns[] = $column['Field'];
        }
    } else {
        foreach ($pdo->query('PRAGMA table_info(appointment_slots)')->fetchAll() as $column) {
            $columns[] = $column['name'];
        }
    }

    $string = $driver === 'mysql' ? 'VARCHAR(191)' : 'TEXT';
    if (!in_array('source_label', $columns, true)) {
        $pdo->exec("ALTER TABLE appointment_slots ADD COLUMN source_label {$string} NULL");
    }
    if (!in_array('raw_payload', $columns, true)) {
        $pdo->exec('ALTER TABLE appointment_slots ADD COLUMN raw_payload TEXT NULL');
    }
};
