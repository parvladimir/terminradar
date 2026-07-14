<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Csrf;
use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\NotificationRepository;

final class NotificationController extends Controller
{
    public function test(Request $request, string $channel): Response
    {
        $user = $this->currentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }
        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/dashboard');
        }

        $status = 'pending';
        $error = null;
        if ($channel === 'telegram' && empty($user['telegram_chat_id'])) {
            $status = 'blocked';
            $error = 'Telegram is not linked yet.';
        }
        if ($channel === 'web_push' && (int) ($user['web_push_enabled'] ?? 0) !== 1) {
            $status = 'blocked';
            $error = 'Web Push is not enabled yet.';
        }

        (new NotificationRepository($this->app->database->pdo()))->create(
            (int) $user['id'],
            null,
            null,
            $channel,
            'TerminRadar test notification',
            'This is a test notification record. External transport delivery is configured in the next transport step.',
            $status,
            $error
        );

        $this->app->session->flash($status === 'blocked' ? 'error' : 'success', $status === 'blocked' ? $error : 'Test notification created.');
        return Response::redirect('/dashboard');
    }

    public function telegramCode(Request $request): Response
    {
        $user = $this->currentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }
        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/dashboard');
        }

        $code = strtoupper(bin2hex(random_bytes(3)));
        $stmt = $this->app->database->pdo()->prepare('UPDATE users SET telegram_link_code = :code, telegram_link_expires_at = :expires, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $user['id'],
            'code' => $code,
            'expires' => date('c', time() + 900),
            'updated_at' => date('c'),
        ]);

        $this->app->session->flash('success', 'Telegram link code: ' . $code);
        return Response::redirect('/dashboard');
    }

    public function confirmTelegramLocal(Request $request): Response
    {
        $user = $this->currentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }
        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/dashboard');
        }

        $code = strtoupper(trim((string) $request->input('code', '')));
        if ($code === '' || $code !== (string) ($user['telegram_link_code'] ?? '') || strtotime((string) ($user['telegram_link_expires_at'] ?? '')) < time()) {
            $this->app->session->flash('error', 'Telegram code is invalid or expired.');
            return Response::redirect('/dashboard');
        }

        $stmt = $this->app->database->pdo()->prepare('UPDATE users SET telegram_chat_id = :chat_id, telegram_verified_at = :verified_at, telegram_link_code = NULL, telegram_link_expires_at = NULL, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $user['id'],
            'chat_id' => 'local-test-' . $user['id'],
            'verified_at' => date('c'),
            'updated_at' => date('c'),
        ]);

        $this->app->session->flash('success', 'Telegram connected locally.');
        return Response::redirect('/dashboard');
    }

    public function enableWebPush(Request $request): Response
    {
        $user = $this->currentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }
        if (Csrf::validate($this->app->session, $request->input('_token'))) {
            $stmt = $this->app->database->pdo()->prepare('UPDATE users SET web_push_enabled = 1, web_push_subscription = :subscription, updated_at = :updated_at WHERE id = :id');
            $stmt->execute([
                'id' => $user['id'],
                'subscription' => json_encode(['local_placeholder' => true, 'enabled_at' => date('c')], JSON_THROW_ON_ERROR),
                'updated_at' => date('c'),
            ]);
            $this->app->session->flash('success', 'Web Push marked as enabled for local testing.');
        }

        return Response::redirect('/dashboard');
    }
}
