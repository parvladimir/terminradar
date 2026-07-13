<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class Translator
{
    /** @var array<string, array<string, mixed>> */
    private array $loaded = [];

    public function __construct(
        private readonly string $basePath,
        private readonly Config $config,
        private readonly Session $session
    ) {
    }

    public function locale(): string
    {
        $locale = (string) $this->session->get('locale', $this->config->get('app.default_locale', 'uk'));
        return in_array($locale, $this->config->get('app.locales', ['uk']), true) ? $locale : 'uk';
    }

    public function setLocale(string $locale): void
    {
        if (in_array($locale, $this->config->get('app.locales', ['uk']), true)) {
            $this->session->put('locale', $locale);
        }
    }

    public function get(string $key): string
    {
        [$file, $path] = array_pad(explode('.', $key, 2), 2, '');
        $locale = $this->locale();
        $value = $this->resolve($this->load($locale, $file), $path);

        if ($value === null) {
            $value = $this->resolve($this->load($locale, 'app'), $key);
        }

        return is_scalar($value) ? (string) $value : $key;
    }

    /** @param array<string, mixed> $catalog */
    private function resolve(array $catalog, string $path): mixed
    {
        if ($path === '') {
            return null;
        }

        $value = $catalog;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /** @return array<string, mixed> */
    private function load(string $locale, string $file): array
    {
        $cacheKey = $locale . '/' . $file;
        if (!array_key_exists($cacheKey, $this->loaded)) {
            $path = $this->basePath . '/lang/' . $locale . '/' . $file . '.php';
            $this->loaded[$cacheKey] = is_file($path) ? require $path : [];
        }
        return $this->loaded[$cacheKey];
    }
}
