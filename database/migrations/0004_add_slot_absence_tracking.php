<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    if ($driver === 'mysql') {
        $stmt = $pdo->query("SHOW COLUMNS FROM appointment_slots LIKE 'absent_confirmations'");
        $exists = (bool) $stmt->fetch();
    } else {
        $columns = $pdo->query('PRAGMA table_info(appointment_slots)')->fetchAll();
        $exists = in_array('absent_confirmations', array_column($columns, 'name'), true);
    }

    if (!$exists) {
        $pdo->exec('ALTER TABLE appointment_slots ADD COLUMN absent_confirmations INTEGER NOT NULL DEFAULT 0');
    }
};
