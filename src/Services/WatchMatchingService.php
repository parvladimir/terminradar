<?php

declare(strict_types=1);

namespace TerminRadar\Services;

use DateTimeImmutable;
use PDO;
use TerminRadar\Repositories\NotificationRepository;
use TerminRadar\Repositories\WatchRepository;

final class WatchMatchingService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function matchSource(int $sourceId): int
    {
        $stmt = $this->pdo->prepare("SELECT s.*, p.id AS practice_id, p.name AS practice_name, p.city, p.postal_code, p.insurance_types FROM appointment_slots s INNER JOIN appointment_sources src ON src.id = s.appointment_source_id INNER JOIN practices p ON p.id = src.practice_id WHERE s.appointment_source_id = :source_id AND s.status = 'available'");
        $stmt->execute(['source_id' => $sourceId]);
        $matches = 0;

        foreach ($stmt->fetchAll() as $slot) {
            foreach ((new WatchRepository($this->pdo))->activeCandidatesForSlot($slot) as $watch) {
                if (!$this->slotMatchesWatch($slot, $watch)) {
                    continue;
                }
                if ($this->createMatchAndNotification($slot, $watch)) {
                    $matches++;
                }
            }
        }

        return $matches;
    }

    /** @param array<string, mixed> $slot @param array<string, mixed> $watch */
    public function slotMatchesWatch(array $slot, array $watch): bool
    {
        $startsAt = new DateTimeImmutable((string) $slot['starts_at']);
        $date = $startsAt->format('Y-m-d');
        $time = $startsAt->format('H:i:s');

        if (!empty($watch['earliest_date']) && $date < $watch['earliest_date']) {
            return false;
        }
        if (!empty($watch['latest_date']) && $date > $watch['latest_date']) {
            return false;
        }
        if (!empty($watch['desired_before_date']) && $date >= $watch['desired_before_date']) {
            return false;
        }
        if (!empty($watch['time_from']) && $time < $watch['time_from']) {
            return false;
        }
        if (!empty($watch['time_to']) && $time > $watch['time_to']) {
            return false;
        }
        if (!empty($watch['city']) && mb_strtolower((string) $slot['city']) !== mb_strtolower((string) $watch['city'])) {
            return false;
        }
        if (!empty($watch['postal_code']) && (string) $slot['postal_code'] !== (string) $watch['postal_code']) {
            return false;
        }
        if (!empty($watch['insurance_type']) && !str_contains(mb_strtolower((string) $slot['insurance_types']), mb_strtolower((string) $watch['insurance_type']))) {
            return false;
        }

        return true;
    }

    /** @param array<string, mixed> $slot @param array<string, mixed> $watch */
    private function createMatchAndNotification(array $slot, array $watch): bool
    {
        $exists = $this->pdo->prepare('SELECT id FROM watch_matches WHERE watch_id = :watch_id AND appointment_slot_id = :slot_id LIMIT 1');
        $exists->execute(['watch_id' => $watch['id'], 'slot_id' => $slot['id']]);
        if ($exists->fetchColumn()) {
            return false;
        }

        $now = date('c');
        $stmt = $this->pdo->prepare("INSERT INTO watch_matches (watch_id, appointment_slot_id, matched_at, status, created_at, updated_at) VALUES (:watch_id, :slot_id, :matched_at, 'new', :created_at, :updated_at)");
        $stmt->execute(['watch_id' => $watch['id'], 'slot_id' => $slot['id'], 'matched_at' => $now, 'created_at' => $now, 'updated_at' => $now]);

        $channels = ['in_app'];
        if ((int) $watch['notification_email'] === 1) {
            $channels[] = 'email';
        }
        if ((int) $watch['notification_telegram'] === 1 && !empty($watch['telegram_chat_id'])) {
            $channels[] = 'telegram';
        }
        if ((int) $watch['notification_web_push'] === 1 && (int) ($watch['web_push_enabled'] ?? 0) === 1) {
            $channels[] = 'web_push';
        }
        $notifications = new NotificationRepository($this->pdo);
        foreach ($channels as $channel) {
            $body = sprintf('Praxis: %s. Termin: %s. Buchung: /slots/%d/book', $slot['practice_name'], $slot['starts_at'], $slot['id']);
            $notifications->create((int) $watch['user_id'], (int) $watch['id'], (int) $slot['id'], $channel, 'TerminRadar: neuer passender Termin', $body);
        }

        return true;
    }
}
