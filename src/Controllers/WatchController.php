<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Csrf;
use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Core\Validator;
use TerminRadar\Repositories\PracticeRepository;
use TerminRadar\Repositories\SpecialtyRepository;
use TerminRadar\Repositories\WatchRepository;

final class WatchController extends Controller
{
    public function create(Request $request): Response
    {
        $user = $this->currentUser();
        if ($user === null) {
            $this->app->session->flash('error', 'Please sign in to create monitoring.');
            return Response::redirect('/login');
        }

        $practice = null;
        $practiceId = (int) $request->input('practice_id', 0);
        if ($practiceId > 0) {
            $practice = (new PracticeRepository($this->app->database->pdo()))->find($practiceId, $this->app->translator->locale());
        }

        return $this->view('watches/create', [
            'practice' => $practice,
            'specialties' => (new SpecialtyRepository($this->app->database->pdo()))->active($this->app->translator->locale()),
        ]);
    }

    public function store(Request $request): Response
    {
        $user = $this->currentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }
        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/watches/create');
        }

        $errors = Validator::validate($request->post, [
            'name' => 'required|min:2',
            'frequency_minutes' => 'required',
        ]);
        if ($errors !== []) {
            $this->app->session->flash('error', 'Please check the watch form.');
            return Response::redirect('/watches/create');
        }

        (new WatchRepository($this->app->database->pdo()))->create((int) $user['id'], $request->post);
        $this->app->session->flash('success', 'Watch created.');
        return Response::redirect('/dashboard');
    }

    public function pause(Request $request, string $id): Response
    {
        return $this->setActive($request, (int) $id, false);
    }

    public function resume(Request $request, string $id): Response
    {
        return $this->setActive($request, (int) $id, true);
    }

    public function delete(Request $request, string $id): Response
    {
        $user = $this->currentUser();
        if ($user !== null && Csrf::validate($this->app->session, $request->input('_token'))) {
            (new WatchRepository($this->app->database->pdo()))->delete((int) $user['id'], (int) $id);
        }
        return Response::redirect('/dashboard');
    }

    public function apiIndex(Request $request): Response
    {
        $user = $this->currentUser();
        if ($user === null) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }

        return Response::json(['data' => (new WatchRepository($this->app->database->pdo()))->forUser((int) $user['id'])]);
    }

    private function setActive(Request $request, int $id, bool $active): Response
    {
        $user = $this->currentUser();
        if ($user !== null && Csrf::validate($this->app->session, $request->input('_token'))) {
            (new WatchRepository($this->app->database->pdo()))->setActive((int) $user['id'], $id, $active);
        }
        return Response::redirect('/dashboard');
    }
}
