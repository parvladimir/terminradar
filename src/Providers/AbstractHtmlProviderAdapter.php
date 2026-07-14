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
        $slots = [];
        $bookingUrl = (string) ($source['booking_url'] ?? $source['source_url']);

        preg_match_all('/(Montag|Dienstag|Mittwoch|Donnerstag|Freitag|Samstag|Sonntag),\s*(\d{1,2})\.(\d{1,2})\.(\d{4})(.*?)(?=(?:Montag|Dienstag|Mittwoch|Donnerstag|Freitag|Samstag|Sonntag),\s*\d{1,2}\.\d{1,2}\.\d{4}|$)/su', $html, $dayBlocks, PREG_SET_ORDER);
        foreach ($dayBlocks as $block) {
            $weekday = $block[1];
            $date = sprintf('%04d-%02d-%02d', (int) $block[4], (int) $block[3], (int) $block[2]);

            preg_match_all('/<a\b([^>]*)>\s*(\d{1,2})[:.](\d{2})\s*<\/a>/iu', $block[5], $linkedMatches, PREG_SET_ORDER);
            foreach ($linkedMatches as $timeMatch) {
                $startsAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', sprintf('%s %02d:%02d', $date, (int) $timeMatch[2], (int) $timeMatch[3]));
                if (!$startsAt) {
                    continue;
                }
                $href = $this->attribute($timeMatch[1], 'href');
                $title = $this->attribute($timeMatch[1], 'title');
                $slotUrl = $href !== null ? $this->absoluteUrl($href, (string) ($source['source_url'] ?? '')) : $bookingUrl;
                $externalId = $href !== null && $href !== '' ? $href : hash('sha256', $startsAt->format('c') . '|' . ($source['id'] ?? 'source'));
                $slots[] = [
                    'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                    'ends_at' => null,
                    'booking_url' => $slotUrl,
                    'external_slot_id' => $externalId,
                    'doctor_name' => $title,
                    'source_label' => 'DocVisit ' . $weekday . ' ' . $startsAt->format('Y-m-d H:i'),
                    'source_url' => (string) ($source['source_url'] ?? ''),
                ];
            }

            $plainBlock = html_entity_decode((string) preg_replace('/<[^>]+>/u', ' ', $block[5]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            preg_match_all('/(?<!\d)(\d{1,2})[:.](\d{2})(?!\d)/u', $plainBlock, $timeMatches, PREG_SET_ORDER);
            foreach ($timeMatches as $timeMatch) {
                $startsAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', sprintf('%s %02d:%02d', $date, (int) $timeMatch[1], (int) $timeMatch[2]));
                if (!$startsAt) {
                    continue;
                }
                $slots[] = [
                    'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                    'ends_at' => null,
                    'booking_url' => $bookingUrl,
                    'external_slot_id' => hash('sha256', $startsAt->format('c') . '|' . ($source['id'] ?? 'source')),
                    'source_label' => 'DocVisit ' . $weekday . ' ' . $startsAt->format('Y-m-d H:i'),
                    'source_url' => (string) ($source['source_url'] ?? ''),
                ];
            }
        }

        if ($slots !== []) {
            return $this->uniqueSlots($slots);
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

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
                'booking_url' => $bookingUrl,
                'external_slot_id' => hash('sha256', $startsAt->format('c') . '|' . ($source['id'] ?? 'source')),
                'source_label' => 'HTML ' . $startsAt->format('Y-m-d H:i'),
                'source_url' => (string) ($source['source_url'] ?? ''),
            ];
            $slots[] = $raw;
        }

        return $this->uniqueSlots($slots);
    }

    private function attribute(string $attributes, string $name): ?string
    {
        if (preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*([\'"])(.*?)\1/iu', $attributes, $match) !== 1) {
            return null;
        }

        return html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function absoluteUrl(string $href, string $baseUrl): string
    {
        if (preg_match('/^https?:\/\//i', $href) === 1) {
            return $href;
        }
        if ($baseUrl === '') {
            return $href;
        }

        $base = parse_url($baseUrl);
        $scheme = (string) ($base['scheme'] ?? 'https');
        $host = (string) ($base['host'] ?? '');
        if ($host === '') {
            return $href;
        }

        if (str_starts_with($href, '/')) {
            return $scheme . '://' . $host . $href;
        }

        $path = (string) ($base['path'] ?? '/');
        $directory = rtrim(str_replace('\\', '/', dirname($path)), '/');
        return $scheme . '://' . $host . ($directory !== '' ? $directory : '') . '/' . ltrim($href, '/');
    }

    /** @param list<array<string, mixed>> $slots @return list<array<string, mixed>> */
    private function uniqueSlots(array $slots): array
    {
        $seen = [];
        $unique = [];
        foreach ($slots as $slot) {
            $key = (string) $slot['starts_at'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $slot;
        }
        return $unique;
    }
}
