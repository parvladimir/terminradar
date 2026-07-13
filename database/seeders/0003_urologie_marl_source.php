<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $now = date('c');
    $practiceStmt = $pdo->prepare('SELECT id FROM practices WHERE slug = :slug');
    $practiceStmt->execute(['slug' => 'urologie-marl-online-testdaten']);
    $practiceId = $practiceStmt->fetchColumn();

    if (!$practiceId) {
        $stmt = $pdo->prepare('INSERT INTO practices (name, slug, description, postal_code, city, website_url, booking_url, source_provider, source_external_id, insurance_types, languages, is_verified, is_active, is_test_data, created_at, updated_at) VALUES (:name, :slug, :description, :postal_code, :city, :website_url, :booking_url, :source_provider, :source_external_id, :insurance_types, :languages, 0, 1, 1, :created_at, :updated_at)');
        $stmt->execute([
            'name' => 'Urologie Marl Online-Terminquelle',
            'slug' => 'urologie-marl-online-testdaten',
            'description' => 'Neutraler Testdatensatz für die erste DocVisit-Quelle. Keine erfundenen Arztangaben.',
            'postal_code' => '45768',
            'city' => 'Marl',
            'website_url' => 'https://www.uro-logisch.de/marl/onlineterminvereinbarung',
            'booking_url' => 'https://www.uro-logisch.de/marl/onlineterminvereinbarung',
            'source_provider' => 'docvisit',
            'source_external_id' => '2866438',
            'insurance_types' => json_encode(['gesetzlich', 'privat', 'selbstzahler'], JSON_THROW_ON_ERROR),
            'languages' => json_encode(['de'], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $practiceId = (int) $pdo->lastInsertId();
    }

    $sourceStmt = $pdo->prepare('SELECT id FROM appointment_sources WHERE provider = :provider AND external_calendar_id = :external_calendar_id');
    $sourceStmt->execute(['provider' => 'docvisit', 'external_calendar_id' => '2866438']);
    if (!$sourceStmt->fetchColumn()) {
        $stmt = $pdo->prepare('INSERT INTO appointment_sources (practice_id, provider, source_url, external_calendar_id, adapter_class, check_interval_minutes, enabled, created_at, updated_at) VALUES (:practice_id, :provider, :source_url, :external_calendar_id, :adapter_class, 15, 0, :created_at, :updated_at)');
        $stmt->execute([
            'practice_id' => $practiceId,
            'provider' => 'docvisit',
            'source_url' => 'https://www.docvisit.de/kalender/marl/list?type=2866438',
            'external_calendar_id' => '2866438',
            'adapter_class' => 'TerminRadar\\Providers\\DocVisitProviderAdapter',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
