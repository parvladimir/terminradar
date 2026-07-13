<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class AppointmentSourceRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT src.*, p.booking_url, p.name AS practice_name FROM appointment_sources src INNER JOIN practices p ON p.id = src.practice_id WHERE src.id = :id');
        $stmt->execute(['id' => $id]);
        $source = $stmt->fetch();
        return $source ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function due(): array
    {
        $stmt = $this->pdo->query('SELECT src.*, p.booking_url, p.name AS practice_name FROM appointment_sources src INNER JOIN practices p ON p.id = src.practice_id WHERE src.enabled = 1 ORDER BY src.id');
        $sources = $stmt->fetchAll();
        $now = time();

        return array_values(array_filter($sources, static function (array $source) use ($now): bool {
            $last = $source['last_success_at'] ?: $source['last_error_at'] ?: null;
            if (!$last) {
                return true;
            }
            return strtotime((string) $last) <= $now - ((int) $source['check_interval_minutes'] * 60);
        }));
    }

    public function markSuccess(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE appointment_sources SET last_success_at = :now, last_error_at = NULL, last_error_message = NULL, consecutive_failures = 0, updated_at = :now WHERE id = :id');
        $stmt->execute(['id' => $id, 'now' => date('c')]);
    }

    public function markError(int $id, string $message): void
    {
        $stmt = $this->pdo->prepare('UPDATE appointment_sources SET last_error_at = :now, last_error_message = :message, consecutive_failures = consecutive_failures + 1, updated_at = :now WHERE id = :id');
        $stmt->execute(['id' => $id, 'message' => mb_substr($message, 0, 1000), 'now' => date('c')]);
    }

    public function acquireLock(int $id, int $ttlSeconds = 240): bool
    {
        $now = date('c');
        $until = date('c', time() + $ttlSeconds);
        $owner = gethostname() . ':' . getmypid();
        $this->pdo->prepare('DELETE FROM source_locks WHERE appointment_source_id = :id AND locked_until < :now')->execute(['id' => $id, 'now' => $now]);

        try {
            $stmt = $this->pdo->prepare('INSERT INTO source_locks (appointment_source_id, locked_until, owner, created_at, updated_at) VALUES (:id, :locked_until, :owner, :now, :now)');
            $stmt->execute(['id' => $id, 'locked_until' => $until, 'owner' => $owner, 'now' => $now]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function releaseLock(int $id): void
    {
        $this->pdo->prepare('DELETE FROM source_locks WHERE appointment_source_id = :id')->execute(['id' => $id]);
    }
}
