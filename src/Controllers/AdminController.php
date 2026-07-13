<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Csrf;
use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Core\Validator;
use TerminRadar\Repositories\PracticeRepository;
use TerminRadar\Repositories\SpecialtyRepository;
use TerminRadar\Repositories\UserRepository;

final class AdminController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $user = $this->adminUser();
        if ($user === null) {
            return $this->adminRedirect();
        }

        return $this->view('admin/dashboard', ['user' => $user]);
    }

    public function practices(Request $request): Response
    {
        $user = $this->adminUser();
        if ($user === null) {
            return $this->adminRedirect();
        }

        $locale = $this->app->translator->locale();
        $repo = new PracticeRepository($this->app->database->pdo());

        return $this->view('admin/practices', [
            'user' => $user,
            'practices' => $repo->search([], $locale),
            'specialties' => (new SpecialtyRepository($this->app->database->pdo()))->active($locale),
        ]);
    }

    public function storePractice(Request $request): Response
    {
        $user = $this->adminUser();
        if ($user === null) {
            return $this->adminRedirect();
        }

        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/admin/practices');
        }

        $errors = Validator::validate($request->post, [
            'name' => 'required|min:2',
            'city' => 'required|min:2',
            'postal_code' => 'required|min:4',
        ]);

        if ($errors !== []) {
            $this->app->session->flash('error', 'Please check the Praxis form.');
            return Response::redirect('/admin/practices');
        }

        $data = $request->post;
        $data['insurance_types'] = $this->splitList((string) ($data['insurance_types'] ?? 'gesetzlich'));
        $data['languages'] = $this->splitList((string) ($data['languages'] ?? 'de'));
        $practiceId = (new PracticeRepository($this->app->database->pdo()))->createWithSource($data);
        $this->app->session->flash('success', 'Praxis created.');

        return Response::redirect('/practices/' . $practiceId);
    }

    /** @return array<string, mixed>|null */
    private function adminUser(): ?array
    {
        $userId = $this->app->session->get('user_id');
        if (!is_numeric($userId)) {
            return null;
        }

        $user = (new UserRepository($this->app->database->pdo()))->find((int) $userId);
        return (($user['role'] ?? 'user') === 'admin') ? $user : null;
    }

    private function adminRedirect(): Response
    {
        return is_numeric($this->app->session->get('user_id')) ? Response::redirect('/dashboard') : Response::redirect('/login');
    }

    /** @return list<string> */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map(static fn (string $item): string => trim(mb_strtolower($item)), explode(',', $value))));
    }
}
