<?php

declare(strict_types=1);

namespace TerminRadar\Services;

use PDO;
use TerminRadar\Core\HttpClient;

final class TelegramService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly HttpClient $http = new HttpClient()
    ) {
    }

    public function processUpdates(): array
    {
        $token = (string) env('TELEGRAM_BOT_TOKEN', '');
        if ($token === '') {
            return ['processed' => 0, 'linked' => 0, 'error' => 'TELEGRAM_BOT_TOKEN is not configured'];
        }

        $offsetFile = base_path('storage/cache/telegram_update_offset.txt');
        $offset = is_file($offsetFile) ? (int) trim((string) file_get_contents($offsetFile)) : 0;
        $url = 'https://api.telegram.org/bot' . $token . '/getUpdates?timeout=1';
        if ($offset > 0) {
            $url .= '&offset=' . ($offset + 1);
        }

        $response = $this->http->get($url, 10);
        if (!$response->ok) {
            return ['processed' => 0, 'linked' => 0, 'error' => $response->error ?? 'Telegram getUpdates failed'];
        }

        $payload = json_decode($response->body, true);
        if (!is_array($payload) || ($payload['ok'] ?? false) !== true) {
            return ['processed' => 0, 'linked' => 0, 'error' => 'Telegram returned an invalid response'];
        }

        $processed = 0;
        $linked = 0;
        foreach (($payload['result'] ?? []) as $update) {
            $processed++;
            $offset = max($offset, (int) ($update['update_id'] ?? 0));
            $message = $update['message'] ?? null;
            if (!is_array($message)) {
                continue;
            }
            $text = strtoupper(trim((string) ($message['text'] ?? '')));
            $chatId = (string) ($message['chat']['id'] ?? '');
            if ($text === '' || $chatId === '') {
                continue;
            }
            if ($this->linkCode($text, $chatId)) {
                $linked++;
                $this->sendMessage($chatId, 'Telegram verbunden. TerminRadar kann dir jetzt Treffer senden.');
            }
        }

        if ($offset > 0) {
            if (!is_dir(dirname($offsetFile))) {
                mkdir(dirname($offsetFile), 0775, true);
            }
            file_put_contents($offsetFile, (string) $offset);
        }

        return ['processed' => $processed, 'linked' => $linked, 'error' => null];
    }

    public function sendPending(): array
    {
        $stmt = $this->pdo->query("SELECT n.*, u.telegram_chat_id FROM notifications n INNER JOIN users u ON u.id = n.user_id WHERE n.channel = 'telegram' AND n.status = 'pending' ORDER BY n.created_at LIMIT 50");
        $sent = 0;
        $failed = 0;

        foreach ($stmt->fetchAll() as $notification) {
            if (empty($notification['telegram_chat_id'])) {
                $this->mark($notification['id'], 'blocked', 'Telegram chat is not linked.');
                $failed++;
                continue;
            }
            $ok = $this->sendMessage((string) $notification['telegram_chat_id'], (string) $notification['body']);
            if ($ok) {
                $this->mark($notification['id'], 'sent', null);
                $sent++;
            } else {
                $this->mark($notification['id'], 'failed', 'Telegram sendMessage failed.');
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    private function linkCode(string $code, string $chatId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE telegram_link_code = :code AND telegram_link_expires_at >= :now LIMIT 1');
        $stmt->execute(['code' => $code, 'now' => date('c')]);
        $userId = $stmt->fetchColumn();
        if (!$userId) {
            return false;
        }

        $update = $this->pdo->prepare('UPDATE users SET telegram_chat_id = :chat_id, telegram_verified_at = :verified_at, telegram_link_code = NULL, telegram_link_expires_at = NULL, updated_at = :updated_at WHERE id = :id');
        $update->execute(['id' => $userId, 'chat_id' => $chatId, 'verified_at' => date('c'), 'updated_at' => date('c')]);
        return true;
    }

    private function sendMessage(string $chatId, string $text): bool
    {
        $token = (string) env('TELEGRAM_BOT_TOKEN', '');
        if ($token === '') {
            return false;
        }

        $query = http_build_query(['chat_id' => $chatId, 'text' => $text, 'disable_web_page_preview' => 'false']);
        $response = $this->http->get('https://api.telegram.org/bot' . $token . '/sendMessage?' . $query, 10);
        return $response->ok && str_contains($response->body, '"ok":true');
    }

    private function mark(int $notificationId, string $status, ?string $error): void
    {
        $stmt = $this->pdo->prepare('UPDATE notifications SET status = :status, sent_at = :sent_at, error_message = :error, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $notificationId,
            'status' => $status,
            'sent_at' => $status === 'sent' ? date('c') : null,
            'error' => $error,
            'updated_at' => date('c'),
        ]);
    }
}
