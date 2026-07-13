<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Application;
use TerminRadar\Core\Response;

abstract class Controller
{
    public function __construct(protected readonly Application $app)
    {
    }

    /** @param array<string, mixed> $data */
    protected function view(string $template, array $data = []): Response
    {
        return new Response($this->app->view->render($template, $data));
    }
}
