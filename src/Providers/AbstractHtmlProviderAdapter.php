<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

use DateTimeImmutable;
use TerminRadar\Core\HttpClient;

abstract class AbstractHtmlProviderAdapter implements AppointmentProviderInterface
{
    public function __construct(protected readonly HttpClient $http = new HttpClient())
    {
    }

    public function fetchAppointmentTypes(array $source): array
    {
        return [];
    }

    public function fetchAvailableSlots(array $source): array
    {
        $html = $source['fixture_html'] ?? null;

        if (!is_string($html)) {
            $response = $this->http->get((string) $source['source_url']);
            if (!$response->ok) {
                throw new ProviderFetchException($response->error ?? 'Source fetch failed.', $response->status);
            }
            $html = $response->body;
        }

        return $this->parseSlots($html, $source);
    }

    public function validateSource(array $source): ProviderValidationResult
    {
        if (($source['source_url'] ?? '') === '') {
            return new ProviderValidationResult(false, 'Source URL is missing.');
        }

        if (isset($source['fixture_html'])) {
            return new ProviderValidationResult(true, 'Fixture source is valid.', 200);
        }

        $response = $this->http->get((string) $source['source_url'], 8);
        return new ProviderValidationResult($response->ok, $response->ok ? 'Source is reachable.' : ($response->error ?? 'Source is not reachable.'), $response->status);
    }

    /** @return list<array<string, mixed>> */
    protected function parseSlots(string $html, array $source): array
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $slots = [];

        preg_match_all('/(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4}).{0,80}?(\d{1,2})[:.](\d{2})/u', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $year = (int) $match[3];
            $year = $year < 100 ? 2000 + $year : $year;
            $startsAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', sprintf('%04d-%02d-%02d %02d:%02d', $year, (int) $match[2], (int) $match[1], (int) $match[4], (int) $match[5]));
            if (!$startsAt) {
                continue;
            }
            $raw = [
                'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                'ends_at' => null,
                'booking_url' => (string) ($source['booking_url'] ?? $source['source_url']),
                'external_slot_id' => hash('sha256', $startsAt->format('c') . '|' . ($source['id'] ?? 'source')),
            ];
            $slots[] = $raw;
        }

        return $slots;
    }
}
