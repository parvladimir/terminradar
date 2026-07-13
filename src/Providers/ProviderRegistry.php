<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

final class ProviderRegistry
{
    /** @var list<AppointmentProviderInterface> */
    private array $adapters;

    public function __construct()
    {
        $this->adapters = [
            new DocVisitProviderAdapter(),
            new GenericHtmlProviderAdapter(),
            new ManualProviderAdapter(),
        ];
    }

    public function forSource(array $source): AppointmentProviderInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($source)) {
                return $adapter;
            }
        }

        throw new ProviderFetchException('No adapter supports provider: ' . (string) ($source['provider'] ?? 'unknown'));
    }
}
