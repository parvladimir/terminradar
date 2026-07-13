<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class ProviderLogRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(int $sourceId, string $status, int $httpStatus, int $appointmentsFound, int $durationMs, ?string $errorMessage, ?string $responseHash): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO provider_logs (appointment_source_id, status, http_status, appointments_found, duration_ms, error_message, response_hash, created_at) VALUES (:source_id, :status, :http_status, :appointments_found, :duration_ms, :error_message, :response_hash, :created_at)');
        $stmt->execute([
            'source_id' => $sourceId,
            'status' => $status,
            'http_status' => $httpStatus ?: null,
            'appointments_found' => $appointmentsFound,
            'duration_ms' => $durationMs,
            'error_message' => $errorMessage,
            'response_hash' => $responseHash,
            'created_at' => date('c'),
        ]);
    }
}
