<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        private readonly string $content,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'text/html; charset=UTF-8']
    ) {
    }

    public static function redirect(string $to): self
    {
        return new self('', 302, ['Location' => $to]);
    }

    /** @param array<string, mixed>|list<mixed> $payload */
    public static function json(array $payload, int $status = 200): self
    {
        return new self(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), $status, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->content;
    }
}
