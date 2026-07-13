<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class WatchRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT w.*, p.name AS practice_name, ms.name_de AS specialty_name_de, ms.name_uk AS specialty_name_uk, ms.name_ru AS specialty_name_ru FROM watches w LEFT JOIN practices p ON p.id = w.practice_id LEFT JOIN medical_specialties ms ON ms.id = w.specialty_id WHERE w.user_id = :user_id ORDER BY w.active DESC, w.created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function create(int $userId, array $data): int
    {
        $now = date('c');
        $stmt = $this->pdo->prepare('INSERT INTO watches (user_id, name, specialty_id, practice_id, city, postal_code, earliest_date, latest_date, desired_before_date, allowed_weekdays, time_from, time_to, insurance_type, only_new_patients, frequency_minutes, notification_email, notification_telegram, notification_web_push, active, expires_at, created_at, updated_at) VALUES (:user_id, :name, :specialty_id, :practice_id, :city, :postal_code, :earliest_date, :latest_date, :desired_before_date, :allowed_weekdays, :time_from, :time_to, :insurance_type, :only_new_patients, :frequency_minutes, :notification_email, :notification_telegram, :notification_web_push, 1, :expires_at, :created_at, :updated_at)');
        $stmt->execute([
            'user_id' => $userId,
            'name' => trim((string) $data['name']),
            'specialty_id' => $this->nullableInt($data['specialty_id'] ?? null),
            'practice_id' => $this->nullableInt($data['practice_id'] ?? null),
            'city' => $this->nullableString($data['city'] ?? null),
            'postal_code' => $this->nullableString($data['postal_code'] ?? null),
            'earliest_date' => $this->nullableString($data['earliest_date'] ?? null),
            'latest_date' => $this->nullableString($data['latest_date'] ?? null),
            'desired_before_date' => $this->nullableString($data['desired_before_date'] ?? null),
            'allowed_weekdays' => isset($data['allowed_weekdays']) ? json_encode(array_values((array) $data['allowed_weekdays']), JSON_THROW_ON_ERROR) : null,
            'time_from' => $this->nullableString($data['time_from'] ?? null),
            'time_to' => $this->nullableString($data['time_to'] ?? null),
            'insurance_type' => $this->nullableString($data['insurance_type'] ?? null),
            'only_new_patients' => isset($data['only_new_patients']) ? 1 : 0,
            'frequency_minutes' => (int) ($data['frequency_minutes'] ?? 15),
            'notification_email' => isset($data['notification_email']) ? 1 : 0,
            'notification_telegram' => isset($data['notification_telegram']) ? 1 : 0,
            'notification_web_push' => isset($data['notification_web_push']) ? 1 : 0,
            'expires_at' => $this->nullableString($data['expires_at'] ?? null),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function setActive(int $userId, int $watchId, bool $active): void
    {
        $stmt = $this->pdo->prepare('UPDATE watches SET active = :active, updated_at = :updated_at WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['active' => $active ? 1 : 0, 'updated_at' => date('c'), 'id' => $watchId, 'user_id' => $userId]);
    }

    public function delete(int $userId, int $watchId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM watches WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $watchId, 'user_id' => $userId]);
    }

    /** @return list<array<string, mixed>> */
    public function activeCandidatesForSlot(array $slot): array
    {
        $stmt = $this->pdo->prepare('SELECT w.*, u.email, u.telegram_chat_id, u.web_push_enabled FROM watches w INNER JOIN users u ON u.id = w.user_id WHERE w.active = 1 AND (w.expires_at IS NULL OR w.expires_at >= :now) AND (w.practice_id IS NULL OR w.practice_id = :practice_id)');
        $stmt->execute(['now' => date('c'), 'practice_id' => $slot['practice_id'] ?? null]);
        return $stmt->fetchAll();
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value !== '' ? $value : null;
    }
}
