<?php

declare(strict_types=1);

namespace TerminRadar\Services;

use TerminRadar\Repositories\UserRepository;

final class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /** @param array<string, mixed> $data @return array<string, mixed>|null */
    public function register(array $data, string $locale): ?array
    {
        if ($this->users->findByEmail((string) $data['email']) !== null) {
            return null;
        }

        $id = $this->users->create([
            'name' => trim((string) $data['name']),
            'email' => trim((string) $data['email']),
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'locale' => $locale,
        ]);

        return $this->users->find($id);
    }

    /** @return array<string, mixed>|null */
    public function attempt(string $email, string $password): ?array
    {
        $user = $this->users->findByEmail($email);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            return null;
        }
        return $user;
    }
}
