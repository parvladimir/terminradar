<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Csrf;
use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Core\Validator;
use TerminRadar\Repositories\UserRepository;
use TerminRadar\Services\AuthService;

final class AuthController extends Controller
{
    public function showRegister(Request $request): Response
    {
        return $this->view('auth/register');
    }

    public function register(Request $request): Response
    {
        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/register');
        }

        $errors = Validator::validate($request->post, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:10',
            'privacy' => 'accepted',
        ]);

        if ($errors !== []) {
            $this->app->session->flash('error', 'Please check the form fields.');
            return Response::redirect('/register');
        }

        $service = new AuthService(new UserRepository($this->app->database->pdo()));
        if ($service->register($request->post, $this->app->translator->locale()) === null) {
            $this->app->session->flash('error', 'Email already exists.');
            return Response::redirect('/register');
        }

        $user = $service->attempt((string) $request->input('email'), (string) $request->input('password'));
        $this->app->session->put('user_id', $user['id']);
        $this->app->session->flash('success', 'Account created.');
        return Response::redirect('/dashboard');
    }

    public function showLogin(Request $request): Response
    {
        return $this->view('auth/login');
    }

    public function login(Request $request): Response
    {
        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/login');
        }

        $service = new AuthService(new UserRepository($this->app->database->pdo()));
        $user = $service->attempt((string) $request->input('email'), (string) $request->input('password'));
        if ($user === null) {
            $this->app->session->flash('error', 'Invalid login.');
            return Response::redirect('/login');
        }

        $this->app->session->put('user_id', $user['id']);
        $this->app->translator->setLocale((string) $user['locale']);
        return Response::redirect('/dashboard');
    }

    public function logout(Request $request): Response
    {
        if (Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->forget('user_id');
        }
        return Response::redirect('/');
    }
}
