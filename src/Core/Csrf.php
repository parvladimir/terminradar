<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class Csrf
{
    public static function token(Session $session): string
    {
        $token = $session->get('_csrf');
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $session->put('_csrf', $token);
        }
        return $token;
    }

    public static function validate(Session $session, mixed $token): bool
    {
        return is_string($token) && hash_equals((string) $session->get('_csrf', ''), $token);
    }
}
