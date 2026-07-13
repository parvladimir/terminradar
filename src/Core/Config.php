<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class Config
{
    /** @var array<string, mixed> */
    private array $items = [];

    public function __construct(private readonly string $basePath)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);
        if (!array_key_exists($file, $this->items)) {
            $configFile = $this->basePath . '/config/' . $file . '.php';
            $this->items[$file] = is_file($configFile) ? require $configFile : [];
        }

        $value = $this->items[$file];
        if ($path === null) {
            return $value;
        }

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
