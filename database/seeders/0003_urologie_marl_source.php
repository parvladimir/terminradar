<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $now = date('c');
    $practiceData = [
        'name' => 'Urologische Praxis Marl',
        'slug' => 'urologische-praxis-marl',
        'description' => 'Urologische Praxis im Gesundheitszentrum Marl an der Paracelsusklinik. Kontaktdaten stammen von der offiziellen Praxis-Website.',
        'street' => 'Lipper Weg',
        'house_number' => '11a',
        'postal_code' => '45770',
        'city' => 'Marl',
        'phone' => '02365/34633',
        'email' => 'rezeptanforderung-marl@uro-logisch.de',
        'website_url' => 'https://www.uro-logisch.de/marl/praxis',
        'booking_url' => 'https://www.uro-logisch.de/marl/onlineterminvereinbarung',
        'source_provider' => 'docvisit',
        'source_external_id' => '2866438',
        'insurance_types' => json_encode(['gesetzlich', 'privat', 'selbstzahler'], JSON_THROW_ON_ERROR),
        'languages' => json_encode(['de'], JSON_THROW_ON_ERROR),
    ];

    $practiceStmt = $pdo->prepare('SELECT id FROM practices WHERE slug = :slug');
    $practiceStmt->execute(['slug' => $practiceData['slug']]);
    $practiceId = $practiceStmt->fetchColumn();

    if (!$practiceId) {
        $legacy = $pdo->prepare('SELECT id FROM practices WHERE slug = :slug');
        $legacy->execute(['slug' => 'urologie-marl-online-testdaten']);
        $practiceId = $legacy->fetchColumn();
    }

    if (!$practiceId) {
        $stmt = $pdo->prepare('INSERT INTO practices (name, slug, description, street, house_number, postal_code, city, phone, email, website_url, booking_url, source_provider, source_external_id, insurance_types, languages, wheelchair_accessible, is_verified, is_active, is_test_data, created_at, updated_at) VALUES (:name, :slug, :description, :street, :house_number, :postal_code, :city, :phone, :email, :website_url, :booking_url, :source_provider, :source_external_id, :insurance_types, :languages, 1, 1, 1, 0, :created_at, :updated_at)');
        $stmt->execute($practiceData + ['created_at' => $now, 'updated_at' => $now]);
        $practiceId = (int) $pdo->lastInsertId();
    } else {
        $stmt = $pdo->prepare('UPDATE practices SET name = :name, slug = :slug, description = :description, street = :street, house_number = :house_number, postal_code = :postal_code, city = :city, phone = :phone, email = :email, website_url = :website_url, booking_url = :booking_url, source_provider = :source_provider, source_external_id = :source_external_id, insurance_types = :insurance_types, languages = :languages, wheelchair_accessible = 1, is_verified = 1, is_test_data = 0, updated_at = :updated_at WHERE id = :id');
        $stmt->execute($practiceData + ['id' => $practiceId, 'updated_at' => $now]);
    }

    $sourceStmt = $pdo->prepare('SELECT id FROM appointment_sources WHERE provider = :provider AND external_calendar_id = :external_calendar_id');
    $sourceStmt->execute(['provider' => 'docvisit', 'external_calendar_id' => '2866438']);
    $sourceId = $sourceStmt->fetchColumn();

    if (!$sourceId) {
        $stmt = $pdo->prepare('INSERT INTO appointment_sources (practice_id, provider, source_url, external_calendar_id, adapter_class, check_interval_minutes, enabled, created_at, updated_at) VALUES (:practice_id, :provider, :source_url, :external_calendar_id, :adapter_class, 15, 1, :created_at, :updated_at)');
        $stmt->execute([
            'practice_id' => $practiceId,
            'provider' => 'docvisit',
            'source_url' => 'https://www.docvisit.de/kalender/marl/list?type=2866438',
            'external_calendar_id' => '2866438',
            'adapter_class' => 'TerminRadar\\Providers\\DocVisitProviderAdapter',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } else {
        $stmt = $pdo->prepare('UPDATE appointment_sources SET practice_id = :practice_id, source_url = :source_url, adapter_class = :adapter_class, check_interval_minutes = 15, enabled = 1, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $sourceId,
            'practice_id' => $practiceId,
            'source_url' => 'https://www.docvisit.de/kalender/marl/list?type=2866438',
            'adapter_class' => 'TerminRadar\\Providers\\DocVisitProviderAdapter',
            'updated_at' => $now,
        ]);
    }

    $specialtyId = $pdo->query("SELECT id FROM medical_specialties WHERE slug = 'urologie'")->fetchColumn();
    if ($specialtyId) {
        $exists = $pdo->prepare('SELECT practice_id FROM practice_specialty WHERE practice_id = :practice_id AND specialty_id = :specialty_id');
        $exists->execute(['practice_id' => $practiceId, 'specialty_id' => $specialtyId]);
        if (!$exists->fetchColumn()) {
            $stmt = $pdo->prepare('INSERT INTO practice_specialty (practice_id, specialty_id) VALUES (:practice_id, :specialty_id)');
            $stmt->execute(['practice_id' => $practiceId, 'specialty_id' => $specialtyId]);
        }
    }
};
