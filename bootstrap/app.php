<?php

declare(strict_types=1);

use TerminRadar\Core\Application;
use TerminRadar\Core\Env;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : '');
}

function env(string $key, mixed $default = null): mixed
{
    return Env::get($key, $default);
}

Env::load(base_path('.env'));

date_default_timezone_set((require base_path('config/app.php'))['timezone'] ?? 'Europe/Berlin');

return new Application(BASE_PATH);
