<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Core\Validator;
use TerminRadar\Repositories\ApiTokenRepository;
use TerminRadar\Repositories\PracticeRepository;
use TerminRadar\Repositories\SpecialtyRepository;
use TerminRadar\Repositories\UserRepository;
use TerminRadar\Repositories\WatchRepository;
use TerminRadar\Services\AuthService;

final class ApiController extends Controller
{
    public function register(Request $request): Response
    {
        $errors = Validator::validate($request->post, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:10',
            'privacy' => 'accepted',
        ]);
        if ($errors !== []) {
            return Response::json(['message' => 'Validation failed', 'errors' => $errors], 422);
        }

        $auth = new AuthService(new UserRepository($this->app->database->pdo()));
        $user = $auth->register($request->post, (string) $request->input('locale', 'uk'));
        if ($user === null) {
            return Response::json(['message' => 'Email already exists'], 409);
        }

        $token = (new ApiTokenRepository($this->app->database->pdo()))->create((int) $user['id'], 'mobile');
        return Response::json(['data' => $this->userResource($user), 'token' => $token], 201);
    }

    public function login(Request $request): Response
    {
        $user = (new AuthService(new UserRepository($this->app->database->pdo())))->attempt((string) $request->input('email'), (string) $request->input('password'));
        if ($user === null) {
            return Response::json(['message' => 'Invalid credentials'], 401);
        }

        $token = (new ApiTokenRepository($this->app->database->pdo()))->create((int) $user['id'], 'mobile');
        return Response::json(['data' => $this->userResource($user), 'token' => $token]);
    }

    public function logout(Request $request): Response
    {
        (new ApiTokenRepository($this->app->database->pdo()))->revoke($request->bearerToken());
        return Response::json(['data' => ['revoked' => true]]);
    }

    public function me(Request $request): Response
    {
        $user = $this->apiUser($request);
        return $user ? Response::json(['data' => $this->userResource($user)]) : Response::json(['message' => 'Unauthenticated'], 401);
    }

    public function updateMe(Request $request): Response
    {
        $user = $this->apiUser($request);
        if (!$user) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }

        $stmt = $this->app->database->pdo()->prepare('UPDATE users SET name = :name, locale = :locale, timezone = :timezone, email_notifications_enabled = :email_notifications_enabled, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $user['id'],
            'name' => trim((string) $request->input('name', $user['name'])),
            'locale' => trim((string) $request->input('locale', $user['locale'])),
            'timezone' => trim((string) $request->input('timezone', $user['timezone'])),
            'email_notifications_enabled' => (int) (bool) $request->input('email_notifications_enabled', $user['email_notifications_enabled']),
            'updated_at' => date('c'),
        ]);

        $fresh = (new UserRepository($this->app->database->pdo()))->find((int) $user['id']);
        return Response::json(['data' => $this->userResource($fresh ?? $user)]);
    }

    public function exportMe(Request $request): Response
    {
        $user = $this->apiUser($request);
        if (!$user) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }
        $watches = (new WatchRepository($this->app->database->pdo()))->forUser((int) $user['id']);
        return Response::json(['data' => ['user' => $this->userResource($user), 'watches' => $watches]]);
    }

    public function deleteMe(Request $request): Response
    {
        $user = $this->apiUser($request);
        if (!$user) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }
        $pdo = $this->app->database->pdo();
        $pdo->prepare('DELETE FROM api_tokens WHERE user_id = :id')->execute(['id' => $user['id']]);
        $pdo->prepare('DELETE FROM notifications WHERE user_id = :id')->execute(['id' => $user['id']]);
        $pdo->prepare('DELETE FROM watches WHERE user_id = :id')->execute(['id' => $user['id']]);
        $pdo->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $user['id']]);
        return Response::json(['data' => ['deleted' => true]]);
    }

    public function specialties(Request $request): Response
    {
        return Response::json(['data' => (new SpecialtyRepository($this->app->database->pdo()))->active((string) $request->input('locale', 'de'))]);
    }

    public function practices(Request $request): Response
    {
        return Response::json(['data' => (new PracticeRepository($this->app->database->pdo()))->search($request->query, (string) $request->input('locale', 'de'))]);
    }

    public function practice(Request $request, string $id): Response
    {
        $practice = (new PracticeRepository($this->app->database->pdo()))->find((int) $id, (string) $request->input('locale', 'de'));
        return $practice ? Response::json(['data' => $practice]) : Response::json(['message' => 'Not found'], 404);
    }

    public function watches(Request $request): Response
    {
        $user = $this->apiUser($request);
        return $user ? Response::json(['data' => (new WatchRepository($this->app->database->pdo()))->forUser((int) $user['id'])]) : Response::json(['message' => 'Unauthenticated'], 401);
    }

    public function createWatch(Request $request): Response
    {
        $user = $this->apiUser($request);
        if (!$user) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }
        $id = (new WatchRepository($this->app->database->pdo()))->create((int) $user['id'], $request->post);
        return Response::json(['data' => ['id' => $id]], 201);
    }

    public function slots(Request $request): Response
    {
        $stmt = $this->app->database->pdo()->query("SELECT id, starts_at, ends_at, booking_url, status FROM appointment_slots WHERE status = 'available' ORDER BY starts_at LIMIT 100");
        return Response::json(['data' => $stmt->fetchAll()]);
    }

    private function apiUser(Request $request): ?array
    {
        return (new ApiTokenRepository($this->app->database->pdo()))->userForToken($request->bearerToken());
    }

    /** @param array<string, mixed> $user */
    private function userResource(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'locale' => $user['locale'],
            'timezone' => $user['timezone'],
            'email_notifications_enabled' => (bool) $user['email_notifications_enabled'],
            'web_push_enabled' => (bool) $user['web_push_enabled'],
        ];
    }
}
