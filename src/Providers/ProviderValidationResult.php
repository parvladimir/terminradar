<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

final class ProviderValidationResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly string $message,
        public readonly ?int $httpStatus = null
    ) {
    }
}
