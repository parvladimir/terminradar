<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class PracticeRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array<string, mixed> $filters @return list<array<string, mixed>> */
    public function search(array $filters, string $locale = 'uk'): array
    {
        $specialtyColumn = match ($locale) {
            'de' => 'ms.name_de',
            'ru' => 'ms.name_ru',
            default => 'ms.name_uk',
        };

        $where = ['p.is_active = 1'];
        $params = [];

        if (($filters['city'] ?? '') !== '') {
            $where[] = '(LOWER(p.city) LIKE :city OR p.postal_code LIKE :postal_code)';
            $params['city'] = '%' . mb_strtolower((string) $filters['city']) . '%';
            $params['postal_code'] = '%' . (string) $filters['city'] . '%';
        }

        if (($filters['specialty'] ?? '') !== '') {
            $where[] = 'ms.slug = :specialty';
            $params['specialty'] = (string) $filters['specialty'];
        }

        if (($filters['q'] ?? '') !== '') {
            $where[] = '(LOWER(p.name) LIKE :q OR LOWER(COALESCE(p.description, \'\')) LIKE :q)';
            $params['q'] = '%' . mb_strtolower((string) $filters['q']) . '%';
        }

        if (($filters['insurance'] ?? '') !== '') {
            $where[] = 'LOWER(COALESCE(p.insurance_types, \'\')) LIKE :insurance';
            $params['insurance'] = '%' . mb_strtolower((string) $filters['insurance']) . '%';
        }

        if (($filters['language'] ?? '') !== '') {
            $where[] = 'LOWER(COALESCE(p.languages, \'\')) LIKE :language';
            $params['language'] = '%' . mb_strtolower((string) $filters['language']) . '%';
        }

        $sql = "SELECT p.*, GROUP_CONCAT(DISTINCT {$specialtyColumn}) AS specialties, MIN(s.starts_at) AS next_slot_at,
                       COUNT(DISTINCT src.id) AS source_count
                FROM practices p
                LEFT JOIN practice_specialty ps ON ps.practice_id = p.id
                LEFT JOIN medical_specialties ms ON ms.id = ps.specialty_id
                LEFT JOIN appointment_sources src ON src.practice_id = p.id
                LEFT JOIN appointment_slots s ON s.appointment_source_id = src.id AND s.status = 'available'
                WHERE " . implode(' AND ', $where) . '
                GROUP BY p.id
                ORDER BY p.city, p.name';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id, string $locale = 'uk'): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM practices WHERE id = :id AND is_active = 1');
        $stmt->execute(['id' => $id]);
        $practice = $stmt->fetch();
        if (!$practice) {
            return null;
        }

        $specialtyColumn = match ($locale) {
            'de' => 'name_de',
            'ru' => 'name_ru',
            default => 'name_uk',
        };

        $stmt = $this->pdo->prepare("SELECT ms.id, ms.slug, ms.{$specialtyColumn} AS name FROM medical_specialties ms INNER JOIN practice_specialty ps ON ps.specialty_id = ms.id WHERE ps.practice_id = :practice_id ORDER BY ms.{$specialtyColumn}");
        $stmt->execute(['practice_id' => $id]);
        $practice['specialties_list'] = $stmt->fetchAll();

        $stmt = $this->pdo->prepare("SELECT src.*,
            COUNT(DISTINCT s.id) AS slot_count,
            MAX(CASE WHEN s.status = 'available' THEN s.starts_at ELSE NULL END) AS latest_slot_at,
            MAX(log.created_at) AS last_log_at
            FROM appointment_sources src
            LEFT JOIN appointment_slots s ON s.appointment_source_id = src.id
            LEFT JOIN provider_logs log ON log.appointment_source_id = src.id
            WHERE src.practice_id = :practice_id
            GROUP BY src.id
            ORDER BY src.provider, src.id");
        $stmt->execute(['practice_id' => $id]);
        $practice['sources'] = $stmt->fetchAll();

        $stmt = $this->pdo->prepare("SELECT s.* FROM appointment_slots s INNER JOIN appointment_sources src ON src.id = s.appointment_source_id WHERE src.practice_id = :practice_id AND s.status = 'available' ORDER BY s.starts_at");
        $stmt->execute(['practice_id' => $id]);
        $practice['slots'] = $stmt->fetchAll();

        return $practice;
    }

    /** @param array<string, mixed> $data */
    public function createWithSource(array $data): int
    {
        $now = date('c');
        $slug = $this->uniqueSlug($this->slugify((string) $data['name']));
        $stmt = $this->pdo->prepare('INSERT INTO practices (name, slug, description, street, house_number, postal_code, city, phone, email, website_url, booking_url, source_provider, source_external_id, insurance_types, languages, wheelchair_accessible, is_verified, is_active, is_test_data, created_at, updated_at) VALUES (:name, :slug, :description, :street, :house_number, :postal_code, :city, :phone, :email, :website_url, :booking_url, :source_provider, :source_external_id, :insurance_types, :languages, :wheelchair_accessible, :is_verified, 1, :is_test_data, :created_at, :updated_at)');
        $stmt->execute([
            'name' => trim((string) $data['name']),
            'slug' => $slug,
            'description' => trim((string) ($data['description'] ?? '')),
            'street' => trim((string) ($data['street'] ?? '')),
            'house_number' => trim((string) ($data['house_number'] ?? '')),
            'postal_code' => trim((string) ($data['postal_code'] ?? '')),
            'city' => trim((string) ($data['city'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'website_url' => trim((string) ($data['website_url'] ?? '')),
            'booking_url' => trim((string) ($data['booking_url'] ?? '')),
            'source_provider' => trim((string) ($data['provider'] ?? 'manual')),
            'source_external_id' => trim((string) ($data['external_calendar_id'] ?? '')),
            'insurance_types' => json_encode(array_values(array_filter($data['insurance_types'] ?? ['gesetzlich'])), JSON_THROW_ON_ERROR),
            'languages' => json_encode(array_values(array_filter($data['languages'] ?? ['de'])), JSON_THROW_ON_ERROR),
            'wheelchair_accessible' => isset($data['wheelchair_accessible']) ? 1 : 0,
            'is_verified' => isset($data['is_verified']) ? 1 : 0,
            'is_test_data' => isset($data['is_test_data']) ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $practiceId = (int) $this->pdo->lastInsertId();

        if (!empty($data['specialty_id'])) {
            $stmt = $this->pdo->prepare('INSERT INTO practice_specialty (practice_id, specialty_id) VALUES (:practice_id, :specialty_id)');
            $stmt->execute(['practice_id' => $practiceId, 'specialty_id' => (int) $data['specialty_id']]);
        }

        if (!empty($data['source_url'])) {
            $provider = trim((string) ($data['provider'] ?? 'manual'));
            $adapter = match ($provider) {
                'docvisit' => 'TerminRadar\\Providers\\DocVisitProviderAdapter',
                'generic_html' => 'TerminRadar\\Providers\\GenericHtmlProviderAdapter',
                default => 'TerminRadar\\Providers\\ManualProviderAdapter',
            };
            $stmt = $this->pdo->prepare('INSERT INTO appointment_sources (practice_id, provider, source_url, external_calendar_id, adapter_class, check_interval_minutes, enabled, created_at, updated_at) VALUES (:practice_id, :provider, :source_url, :external_calendar_id, :adapter_class, :check_interval_minutes, :enabled, :created_at, :updated_at)');
            $stmt->execute([
                'practice_id' => $practiceId,
                'provider' => $provider,
                'source_url' => trim((string) $data['source_url']),
                'external_calendar_id' => trim((string) ($data['external_calendar_id'] ?? '')),
                'adapter_class' => $adapter,
                'check_interval_minutes' => (int) ($data['check_interval_minutes'] ?? 15),
                'enabled' => isset($data['source_enabled']) ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $practiceId;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'praxis';
        $candidate = $slug;
        $i = 2;
        while (true) {
            $stmt = $this->pdo->prepare('SELECT id FROM practices WHERE slug = :slug');
            $stmt->execute(['slug' => $candidate]);
            if (!$stmt->fetchColumn()) {
                return $candidate;
            }
            $candidate = $slug . '-' . $i;
            $i++;
        }
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? '');
        return trim($value, '-');
    }
}
