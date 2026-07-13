<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password_hash, role, locale, timezone, email_notifications_enabled, web_push_enabled, consent_at, privacy_version, created_at, updated_at) VALUES (:name, :email, :password_hash, :role, :locale, :timezone, :email_notifications_enabled, :web_push_enabled, :consent_at, :privacy_version, :created_at, :updated_at)');
        $now = date('c');
        $stmt->execute([
            'name' => $data['name'],
            'email' => mb_strtolower((string) $data['email']),
            'password_hash' => $data['password_hash'],
            'role' => $data['role'] ?? 'user',
            'locale' => $data['locale'] ?? 'uk',
            'timezone' => $data['timezone'] ?? 'Europe/Berlin',
            'email_notifications_enabled' => (int) ($data['email_notifications_enabled'] ?? 1),
            'web_push_enabled' => (int) ($data['web_push_enabled'] ?? 0),
            'consent_at' => $data['consent_at'] ?? $now,
            'privacy_version' => $data['privacy_version'] ?? '2026-07-13',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => mb_strtolower($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}
