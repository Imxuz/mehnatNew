<?php

namespace App\Services\Telegram;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $token;
    protected string $baseUrl;
    protected ?string $proxy;

    public function __construct()
    {
        $this->token = config('telegram.bot_token');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
        $this->proxy = config('telegram.proxy');
    }

    /**
     * HTTP mijozi instansiyasini tayyorlash (proxy bo'lsa ulab)
     */
    protected function client(int $timeout = 15): PendingRequest
    {
        $client = Http::timeout($timeout);

        $proxy = config('telegram.proxy');
        if ($proxy) {
            $client->withOptions([
                'proxy' => $proxy,
                'verify' => false,
            ]);
        } else {
            // Maxsus TELEGRAM_PROXY ko'rsatilmagan bo'lsa, to'g'ridan-to'g'ri ulanadi
            $client->withOptions([
                'proxy' => '',
                'verify' => false,
            ]);
        }

        return $client;
    }

    /**
     * Telegram'ga matnli xabar jo'natish (4096 belgidan oshsa avtomatik bo'lib jo'natadi)
     */
    public function sendMessage(
        int|string $chatId,
        string $text,
        ?array $replyMarkup = null,
        string $parseMode = 'HTML',
        bool $disableWebPagePreview = true
    ): array {
        $chunks = $this->chunkMessage($text);
        $lastResponse = ['ok' => true];

        foreach ($chunks as $index => $chunk) {
            $isLastChunk = ($index === count($chunks) - 1);

            $payload = [
                'chat_id' => $chatId,
                'text' => $chunk,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => $disableWebPagePreview,
            ];

            // Tugmalarni faqat oxirgi qismga ulaymiz
            if ($isLastChunk && $replyMarkup !== null) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            try {
                $response = $this->client()->post("{$this->baseUrl}/sendMessage", $payload);
                $body = $response->json() ?? [];

                if (!$response->successful()) {
                    $isBlocked = $response->status() === 403 || str_contains($body['description'] ?? '', 'blocked by the user');
                    if (!$isBlocked && $response->status() !== 400) {
                        Log::warning("Telegram sendMessage failed for chat_id={$chatId}: " . json_encode($body));
                    }
                    return [
                        'ok' => false,
                        'status' => $response->status(),
                        'description' => $body['description'] ?? 'Unknown error',
                        'is_blocked' => $isBlocked,
                    ];
                }

                $lastResponse = $body;
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                $isBlocked = str_contains($msg, '403') || str_contains($msg, 'blocked by the user');
                if (!$isBlocked) {
                    Log::error("Telegram sendMessage exception for chat_id={$chatId}: " . $msg);
                }
                return [
                    'ok' => false,
                    'status' => $isBlocked ? 403 : 500,
                    'description' => $msg,
                    'is_blocked' => $isBlocked,
                ];
            }
        }

        return $lastResponse;
    }

    /**
     * Rasm jo'natish (Fayl yo'li, URL yoki telegram file_id)
     */
    public function sendPhoto(
        int|string $chatId,
        string $photo,
        ?string $caption = null,
        ?array $replyMarkup = null,
        string $parseMode = 'HTML'
    ): array {
        try {
            $client = $this->client(30);

            // Agar rasm lokal fayl bo'lsa (fayl tizimida mavjud)
            if (file_exists($photo)) {
                $response = $client->attach(
                    'photo',
                    file_get_contents($photo),
                    basename($photo)
                )->post("{$this->baseUrl}/sendPhoto", array_filter([
                    'chat_id' => $chatId,
                    'caption' => $caption,
                    'parse_mode' => $parseMode,
                    'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
                ]));
            } else {
                // Rasm file_id yoki tashqi URL
                $payload = array_filter([
                    'chat_id' => $chatId,
                    'photo' => $photo,
                    'caption' => $caption,
                    'parse_mode' => $parseMode,
                    'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
                ]);
                $response = $client->post("{$this->baseUrl}/sendPhoto", $payload);
            }

            $body = $response->json() ?? [];
            if (!$response->successful()) {
                Log::warning("Telegram sendPhoto failed: " . json_encode($body));
                return [
                    'ok' => false,
                    'description' => $body['description'] ?? 'Photo send error',
                ];
            }

            return $body;
        } catch (\Exception $e) {
            Log::error("Telegram sendPhoto exception: " . $e->getMessage());
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     * Hujjat (PDF va b.) jo'natish
     */
    public function sendDocument(
        int|string $chatId,
        string $documentPath,
        ?string $caption = null,
        ?array $replyMarkup = null
    ): array {
        try {
            if (!file_exists($documentPath)) {
                return ['ok' => false, 'description' => 'Document file not found'];
            }

            $response = $this->client(30)->attach(
                'document',
                file_get_contents($documentPath),
                basename($documentPath)
            )->post("{$this->baseUrl}/sendDocument", array_filter([
                'chat_id' => $chatId,
                'caption' => $caption,
                'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
            ]));

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error("Telegram sendDocument exception: " . $e->getMessage());
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     * Long polling orqali xabarlarni olish (php artisan telegram:poll uchun)
     */
    public function getUpdates(int $offset = 0, int $limit = 100, int $timeout = 10): array
    {
        try {
            $response = $this->client($timeout + 5)->get("{$this->baseUrl}/getUpdates", [
                'offset' => $offset,
                'limit' => $limit,
                'timeout' => $timeout,
                'allowed_updates' => json_encode(['message', 'edited_message', 'callback_query']),
            ]);

            $json = $response->json();
            return $json['result'] ?? [];
        } catch (\Exception $e) {
            Log::error("Telegram getUpdates exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Webhook o'rnatish
     */
    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        $payload = ['url' => $url];
        if ($secretToken) {
            $payload['secret_token'] = $secretToken;
        }

        $response = $this->client()->post("{$this->baseUrl}/setWebhook", $payload);
        return $response->json() ?? [];
    }

    /**
     * Webhook'ni o'chirish
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): array
    {
        $response = $this->client()->post("{$this->baseUrl}/deleteWebhook", [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
        return $response->json() ?? [];
    }

    /**
     * Webhook holatini tekshirish
     */
    public function getWebhookInfo(): array
    {
        $response = $this->client()->get("{$this->baseUrl}/getWebhookInfo");
        return $response->json() ?? [];
    }

    /**
     * Bot ma'lumotlarini olish (getMe)
     */
    public function getMe(): array
    {
        $response = $this->client()->get("{$this->baseUrl}/getMe");
        return $response->json() ?? [];
    }

    /**
     * Inline tugma bosilganda Telegram so'rovini qanoatlantirish
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): array
    {
        try {
            $payload = [
                'callback_query_id' => $callbackQueryId,
                'show_alert' => $showAlert,
            ];
            if ($text !== null) {
                $payload['text'] = $text;
            }

            $response = $this->client()->post("{$this->baseUrl}/answerCallbackQuery", $payload);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error("answerCallbackQuery exception: " . $e->getMessage());
            return ['ok' => false];
        }
    }

    /**
     * Telegram xabar uzunligi 4096 belgidan oshmasligi uchun bo'lib berish
     */
    protected function chunkMessage(string $text, int $max = 4096): array
    {
        if (mb_strlen($text) <= $max) {
            return [$text];
        }

        $chunks = [];
        while (mb_strlen($text) > 0) {
            if (mb_strlen($text) <= $max) {
                $chunks[] = $text;
                break;
            }

            // Xabarni so'z yoki qatordan bo'lish
            $chunk = mb_substr($text, 0, $max);
            $lastBreak = mb_strrpos($chunk, "\n");
            if ($lastBreak === false || $lastBreak < ($max - 500)) {
                $lastBreak = mb_strrpos($chunk, " ");
            }

            if ($lastBreak !== false && $lastBreak > 0) {
                $chunk = mb_substr($text, 0, $lastBreak);
                $text = mb_substr($text, $lastBreak);
            } else {
                $text = mb_substr($text, $max);
            }

            $chunks[] = $chunk;
        }

        return $chunks;
    }
}
