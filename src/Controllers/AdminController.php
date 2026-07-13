<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\UserRepository;

final class AdminController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $userId = $this->app->session->get('user_id');
        if (!is_numeric($userId)) {
            return Response::redirect('/login');
        }

        $user = (new UserRepository($this->app->database->pdo()))->find((int) $userId);
        if (($user['role'] ?? 'user') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        return $this->view('admin/dashboard', ['user' => $user]);
    }
}
