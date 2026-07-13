<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class HttpClient
{
    public function get(string $url, int $timeoutSeconds = 12): HttpResponse
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
                'header' => "User-Agent: TerminRadar/0.1 (+https://terminradar.local)\r\nAccept: text/html,application/json;q=0.9,*/*;q=0.8\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        $status = 0;

        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
                $status = (int) $matches[1];
                break;
            }
        }

        if ($body === false) {
            return new HttpResponse('', $status, false, 'Unable to fetch URL.');
        }

        return new HttpResponse($body, $status, $status >= 200 && $status < 300, null);
    }
}
