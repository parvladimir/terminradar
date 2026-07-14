<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\NotificationRepository;
use TerminRadar\Repositories\UserRepository;
use TerminRadar\Repositories\WatchRepository;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $this->app->session->get('user_id');
        if (!is_numeric($userId)) {
            return Response::redirect('/login');
        }

        $user = (new UserRepository($this->app->database->pdo()))->find((int) $userId);
        $notifications = new NotificationRepository($this->app->database->pdo());

        return $this->view('dashboard', [
            'user' => $user,
            'watches' => (new WatchRepository($this->app->database->pdo()))->forUser((int) $userId),
            'matches' => $notifications->matchesForUser((int) $userId),
            'notifications' => $notifications->forUser((int) $userId),
        ]);
    }
}
