<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $indexes = [
        'CREATE INDEX IF NOT EXISTS idx_practices_city ON practices(city)',
        'CREATE INDEX IF NOT EXISTS idx_practices_postal_code ON practices(postal_code)',
        'CREATE INDEX IF NOT EXISTS idx_practices_source_external_id ON practices(source_external_id)',
        'CREATE INDEX IF NOT EXISTS idx_sources_provider ON appointment_sources(provider)',
        'CREATE INDEX IF NOT EXISTS idx_sources_enabled_interval ON appointment_sources(enabled, check_interval_minutes)',
        'CREATE INDEX IF NOT EXISTS idx_slots_starts_at ON appointment_slots(starts_at)',
        'CREATE INDEX IF NOT EXISTS idx_slots_last_seen_at ON appointment_slots(last_seen_at)',
        'CREATE INDEX IF NOT EXISTS idx_watches_user_id ON watches(user_id)',
        'CREATE INDEX IF NOT EXISTS idx_watches_active ON watches(active)',
        'CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id)',
        'CREATE INDEX IF NOT EXISTS idx_provider_logs_source ON provider_logs(appointment_source_id)',
    ];

    foreach ($indexes as $sql) {
        $pdo->exec($sql);
    }
};
