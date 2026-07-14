<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Csrf;
use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\AppointmentSourceRepository;
use TerminRadar\Services\AppointmentCheckService;

final class SourceController extends Controller
{
    public function check(Request $request, string $id): Response
    {
        $user = $this->currentUser();
        if (($user['role'] ?? 'user') !== 'admin') {
            $this->app->session->flash('error', 'Only admin can run source checks from the UI.');
            return Response::redirect('/login');
        }

        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/admin');
        }

        $source = (new AppointmentSourceRepository($this->app->database->pdo()))->find((int) $id);
        if ($source === null) {
            $this->app->session->flash('error', 'Source not found.');
            return Response::redirect('/admin');
        }

        $result = (new AppointmentCheckService($this->app->database->pdo()))->checkSource((int) $id);
        $this->app->session->flash(
            $result['errors'] > 0 ? 'error' : 'success',
            sprintf('Source checked: %d new, %d updated, %d disappeared, %d errors.', $result['new'], $result['updated'], $result['disappeared'], $result['errors'])
        );

        return Response::redirect('/practices/' . $source['practice_id']);
    }
}
