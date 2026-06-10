<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineMessagingService
{
    protected $apiUrl = 'https://api.line.me/v2/bot/message/push';
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = config('services.line.channel_access_token');
    }

    /**
     * Send push message via LINE Messaging API
     *
     * @param string $to LINE User ID
     * @param string $message Text message to send
     * @return bool
     */
    public function send($to, $message)
    {
        $to = trim((string) $to);

        if (empty($this->accessToken)) {
            Log::warning('LINE Messaging: Channel access token is not configured.');
            return false;
        }

        if (empty($to)) {
            Log::warning('LINE Messaging: User ID is missing.');
            return false;
        }

        if (! $this->isValidLineUserId($to)) {
            Log::warning('LINE Messaging: Invalid LINE User ID format.', [
                'line_user_id' => $this->maskLineUserId($to),
            ]);
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'to' => $to,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ]
                ],
            ]);

            if ($response->successful()) {
                Log::info('LINE Messaging: Message sent successfully.', [
                    'line_user_id' => $this->maskLineUserId($to),
                ]);
                return true;
            }

            Log::error('LINE Messaging Error [' . $response->status() . ']: ' . $response->body(), [
                'line_user_id' => $this->maskLineUserId($to),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('LINE Messaging Exception: ' . $e->getMessage());
            return false;
        }
    }

    private function isValidLineUserId(string $userId): bool
    {
        return preg_match('/^U[a-fA-F0-9]{32}$/', $userId) === 1;
    }

    private function maskLineUserId(string $userId): string
    {
        if (strlen($userId) <= 8) {
            return $userId === '' ? '(empty)' : '****';
        }

        return substr($userId, 0, 4) . '...' . substr($userId, -4);
    }
}
