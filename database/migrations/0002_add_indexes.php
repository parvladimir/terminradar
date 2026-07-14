<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    $indexes = [
        ['idx_practices_city', 'practices', 'city'],
        ['idx_practices_postal_code', 'practices', 'postal_code'],
        ['idx_practices_source_external_id', 'practices', 'source_external_id'],
        ['idx_sources_provider', 'appointment_sources', 'provider'],
        ['idx_sources_enabled_interval', 'appointment_sources', 'enabled, check_interval_minutes'],
        ['idx_slots_starts_at', 'appointment_slots', 'starts_at'],
        ['idx_slots_last_seen_at', 'appointment_slots', 'last_seen_at'],
        ['idx_watches_user_id', 'watches', 'user_id'],
        ['idx_watches_active', 'watches', 'active'],
        ['idx_notifications_user_id', 'notifications', 'user_id'],
        ['idx_provider_logs_source', 'provider_logs', 'appointment_source_id'],
    ];

    foreach ($indexes as [$name, $table, $columns]) {
        if ($driver === 'mysql') {
            $stmt = $pdo->prepare('SHOW INDEX FROM ' . $table . ' WHERE Key_name = :name');
            $stmt->execute(['name' => $name]);
            if (!$stmt->fetch()) {
                $pdo->exec("CREATE INDEX {$name} ON {$table}({$columns})");
            }
            continue;
        }

        $pdo->exec("CREATE INDEX IF NOT EXISTS {$name} ON {$table}({$columns})");
    }
};
