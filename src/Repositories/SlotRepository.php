<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class SlotRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findWithPractice(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, src.provider, src.source_url, p.id AS practice_id, p.name AS practice_name, p.city, p.postal_code, p.booking_url AS practice_booking_url FROM appointment_slots s INNER JOIN appointment_sources src ON src.id = s.appointment_source_id INNER JOIN practices p ON p.id = src.practice_id WHERE s.id = :id');
        $stmt->execute(['id' => $id]);
        $slot = $stmt->fetch();
        return $slot ?: null;
    }
}
