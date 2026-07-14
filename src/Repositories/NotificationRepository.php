<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class NotificationRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function forUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('SELECT n.*, s.starts_at, p.name AS practice_name FROM notifications n LEFT JOIN appointment_slots s ON s.id = n.appointment_slot_id LEFT JOIN appointment_sources src ON src.id = s.appointment_source_id LEFT JOIN practices p ON p.id = src.practice_id WHERE n.user_id = :user_id ORDER BY n.created_at DESC LIMIT ' . max(1, $limit));
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function matchesForUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('SELECT wm.*, w.name AS watch_name, s.starts_at, s.id AS slot_id, p.name AS practice_name FROM watch_matches wm INNER JOIN watches w ON w.id = wm.watch_id INNER JOIN appointment_slots s ON s.id = wm.appointment_slot_id INNER JOIN appointment_sources src ON src.id = s.appointment_source_id INNER JOIN practices p ON p.id = src.practice_id WHERE w.user_id = :user_id ORDER BY wm.matched_at DESC LIMIT ' . max(1, $limit));
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, ?int $watchId, ?int $slotId, string $channel, string $subject, string $body, string $status = 'pending', ?string $error = null): void
    {
        $now = date('c');
        $stmt = $this->pdo->prepare('INSERT INTO notifications (user_id, watch_id, appointment_slot_id, channel, subject, body, status, error_message, created_at, updated_at) VALUES (:user_id, :watch_id, :slot_id, :channel, :subject, :body, :status, :error_message, :created_at, :updated_at)');
        $stmt->execute([
            'user_id' => $userId,
            'watch_id' => $watchId,
            'slot_id' => $slotId,
            'channel' => $channel,
            'subject' => $subject,
            'body' => $body,
            'status' => $status,
            'error_message' => $error,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
