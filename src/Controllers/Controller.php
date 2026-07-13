<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Application;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\UserRepository;

abstract class Controller
{
    public function __construct(protected readonly Application $app)
    {
    }

    /** @param array<string, mixed> $data */
    protected function view(string $template, array $data = []): Response
    {
        if (!array_key_exists('currentUser', $data)) {
            $data['currentUser'] = $this->currentUser();
        }

        return new Response($this->app->view->render($template, $data));
    }

    /** @return array<string, mixed>|null */
    protected function currentUser(): ?array
    {
        $userId = $this->app->session->get('user_id');
        if (!is_numeric($userId)) {
            return null;
        }

        return (new UserRepository($this->app->database->pdo()))->find((int) $userId);
    }
}
