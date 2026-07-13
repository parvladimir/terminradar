<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class HttpResponse
{
    public function __construct(
        public readonly string $body,
        public readonly int $status,
        public readonly bool $ok,
        public readonly ?string $error
    ) {
    }
}
