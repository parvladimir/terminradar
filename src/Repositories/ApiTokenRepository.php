<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class ApiTokenRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(int $userId, string $name = 'api'): string
    {
        $plain = bin2hex(random_bytes(32));
        $now = date('c');
        $stmt = $this->pdo->prepare('INSERT INTO api_tokens (user_id, name, token_hash, created_at, updated_at) VALUES (:user_id, :name, :token_hash, :created_at, :updated_at)');
        $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'token_hash' => hash('sha256', $plain),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return $plain;
    }

    /** @return array<string, mixed>|null */
    public function userForToken(?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }
        $stmt = $this->pdo->prepare('SELECT u.* FROM api_tokens t INNER JOIN users u ON u.id = t.user_id WHERE t.token_hash = :hash AND (t.expires_at IS NULL OR t.expires_at >= :now) LIMIT 1');
        $stmt->execute(['hash' => hash('sha256', $token), 'now' => date('c')]);
        $user = $stmt->fetch();
        if ($user) {
            $this->pdo->prepare('UPDATE api_tokens SET last_used_at = :now, updated_at = :now WHERE token_hash = :hash')->execute(['now' => date('c'), 'hash' => hash('sha256', $token)]);
        }
        return $user ?: null;
    }

    public function revoke(?string $token): void
    {
        if ($token === null || $token === '') {
            return;
        }
        $stmt = $this->pdo->prepare('DELETE FROM api_tokens WHERE token_hash = :hash');
        $stmt->execute(['hash' => hash('sha256', $token)]);
    }
}
