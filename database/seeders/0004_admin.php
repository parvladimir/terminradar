<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $email = env('ADMIN_EMAIL', 'admin@example.de');
    $password = env('ADMIN_PASSWORD', 'ChangeMeImmediately123!');
    $name = env('ADMIN_NAME', 'TerminRadar Admin');
    $exists = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $exists->execute(['email' => mb_strtolower($email)]);
    if ($exists->fetchColumn()) {
        return;
    }

    $now = date('c');
    $stmt = $pdo->prepare('INSERT INTO users (name, email, email_verified_at, password_hash, role, locale, timezone, email_notifications_enabled, web_push_enabled, consent_at, privacy_version, created_at, updated_at) VALUES (:name, :email, :email_verified_at, :password_hash, :role, :locale, :timezone, 1, 0, :consent_at, :privacy_version, :created_at, :updated_at)');
    $stmt->execute([
        'name' => $name,
        'email' => mb_strtolower((string) $email),
        'email_verified_at' => $now,
        'password_hash' => password_hash((string) $password, PASSWORD_DEFAULT),
        'role' => 'admin',
        'locale' => 'uk',
        'timezone' => 'Europe/Berlin',
        'consent_at' => $now,
        'privacy_version' => '2026-07-13',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
};
