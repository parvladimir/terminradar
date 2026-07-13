<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;
use TerminRadar\Providers\AppointmentSlotDTO;

final class AppointmentSlotRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param list<AppointmentSlotDTO> $slots @return array{new:int, updated:int, disappeared:int} */
    public function sync(int $sourceId, array $slots): array
    {
        $now = date('c');
        $seenHashes = [];
        $new = 0;
        $updated = 0;

        foreach ($slots as $slot) {
            $seenHashes[] = $slot->rawHash;
            $existing = $this->findExisting($sourceId, $slot);

            if ($existing === null) {
                $stmt = $this->pdo->prepare('INSERT INTO appointment_slots (appointment_source_id, starts_at, ends_at, booking_url, external_slot_id, first_seen_at, last_seen_at, status, raw_hash, absent_confirmations, created_at, updated_at) VALUES (:source_id, :starts_at, :ends_at, :booking_url, :external_slot_id, :first_seen_at, :last_seen_at, :status, :raw_hash, 0, :created_at, :updated_at)');
                $stmt->execute([
                    'source_id' => $sourceId,
                    'starts_at' => $slot->startsAt,
                    'ends_at' => $slot->endsAt,
                    'booking_url' => $slot->bookingUrl,
                    'external_slot_id' => $slot->externalSlotId,
                    'first_seen_at' => $now,
                    'last_seen_at' => $now,
                    'status' => 'available',
                    'raw_hash' => $slot->rawHash,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $new++;
                continue;
            }

            $stmt = $this->pdo->prepare("UPDATE appointment_slots SET last_seen_at = :now, status = 'available', disappeared_at = NULL, absent_confirmations = 0, booking_url = :booking_url, updated_at = :now WHERE id = :id");
            $stmt->execute(['id' => $existing['id'], 'booking_url' => $slot->bookingUrl, 'now' => $now]);
            $updated++;
        }

        $disappeared = $this->markAbsent($sourceId, $seenHashes, $now);

        return ['new' => $new, 'updated' => $updated, 'disappeared' => $disappeared];
    }

    /** @return array<string, mixed>|null */
    private function findExisting(int $sourceId, AppointmentSlotDTO $slot): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM appointment_slots WHERE appointment_source_id = :source_id AND raw_hash = :raw_hash LIMIT 1');
        $stmt->execute(['source_id' => $sourceId, 'raw_hash' => $slot->rawHash]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }

        if ($slot->externalSlotId !== null && $slot->externalSlotId !== '') {
            $stmt = $this->pdo->prepare('SELECT * FROM appointment_slots WHERE appointment_source_id = :source_id AND external_slot_id = :external_slot_id LIMIT 1');
            $stmt->execute(['source_id' => $sourceId, 'external_slot_id' => $slot->externalSlotId]);
            $row = $stmt->fetch();
            return $row ?: null;
        }

        return null;
    }

    /** @param list<string> $seenHashes */
    private function markAbsent(int $sourceId, array $seenHashes, string $now): int
    {
        $stmt = $this->pdo->prepare("SELECT * FROM appointment_slots WHERE appointment_source_id = :source_id AND status = 'available'");
        $stmt->execute(['source_id' => $sourceId]);
        $available = $stmt->fetchAll();
        $disappeared = 0;

        foreach ($available as $slot) {
            if (in_array($slot['raw_hash'], $seenHashes, true)) {
                continue;
            }

            $confirmations = (int) ($slot['absent_confirmations'] ?? 0) + 1;
            if ($confirmations >= 2) {
                $update = $this->pdo->prepare("UPDATE appointment_slots SET status = 'disappeared', disappeared_at = :now, absent_confirmations = :confirmations, updated_at = :now WHERE id = :id");
                $disappeared++;
            } else {
                $update = $this->pdo->prepare('UPDATE appointment_slots SET absent_confirmations = :confirmations, updated_at = :now WHERE id = :id');
            }
            $update->execute(['id' => $slot['id'], 'confirmations' => $confirmations, 'now' => $now]);
        }

        return $disappeared;
    }
}
