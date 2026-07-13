<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

use RuntimeException;

final class ProviderFetchException extends RuntimeException
{
    public function __construct(string $message, public readonly int $httpStatus = 0)
    {
        parent::__construct($message);
    }
}
