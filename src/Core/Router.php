<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    public function __construct(private readonly Application $app)
    {
    }

    /** @param array{0: class-string, 1: string} $handler */
    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /** @param array{0: class-string, 1: string} $handler */
    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $handler = $this->routes[$request->method][$request->path] ?? null;
        $params = [];

        if ($handler === null) {
            foreach ($this->routes[$request->method] ?? [] as $route => $candidate) {
                if (!str_contains($route, '{')) {
                    continue;
                }

                $pattern = '#^' . preg_replace('#\{[^/]+\}#', '([^/]+)', $route) . '$#';
                if (preg_match($pattern, $request->path, $matches) === 1) {
                    array_shift($matches);
                    $handler = $candidate;
                    $params = $matches;
                    break;
                }
            }
        }

        if ($handler === null) {
            return new Response($this->app->view->render('errors/404', ['path' => $request->path]), 404);
        }

        [$class, $method] = $handler;
        return $this->app->controller($class)->{$method}($request, ...$params);
    }
}
