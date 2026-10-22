<?php

namespace App\Services\Telegram;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TelegramAuthSessionService
{
    protected const CACHE_PREFIX = 'telegram_auth_session:';
    protected const TTL_SECONDS = 300; // 5 daqiqa

    /**
     * Yangi Telegram autentifikatsiya sessiyasini yaratish
     */
    public function createSession(string $mode = 'login'): array
    {
        $token = 'auth_' . Str::random(32);
        $expiresAt = now()->addSeconds(self::TTL_SECONDS);

        $sessionData = [
            'token' => $token,
            'mode' => in_array($mode, ['login', 'register']) ? $mode : 'login',
            'status' => 'pending',
            'created_at' => now()->toISOString(),
            'expires_at' => $expiresAt->toISOString(),
        ];

        Cache::put(self::CACHE_PREFIX . $token, $sessionData, self::TTL_SECONDS);

        $botUsername = config('telegram.bot_username');
        if (!$botUsername) {
            // Agar configda ko'rsatilmagan bo'lsa, TelegramBotService dan olamiz yoki default
            $botUsername = 'mehnatngmkuzbot';
        }

        $authUrl = "https://t.me/{$botUsername}?start={$token}";

        return [
            'token' => $token,
            'mode' => $sessionData['mode'],
            'bot_username' => $botUsername,
            'auth_url' => $authUrl,
            'expires_in' => self::TTL_SECONDS,
        ];
    }

    /**
     * Sessiya tokenini bir xil ko'rinishga keltirish (auth_ prefiksi bilan)
     */
    protected function normalizeToken(string $token): string
    {
        $token = trim($token);
        return str_starts_with($token, 'auth_') ? $token : 'auth_' . $token;
    }

    /**
     * Sessiyani keshdan olish
     */
    public function getSession(string $token): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $this->normalizeToken($token));
    }

    /**
     * Sessiyani tasdiqlash (User topilganda/yaratilganda va JWT token berilganda)
     */
    public function confirmSession(string $token, User $user, string $accessToken, string $refreshToken): bool
    {
        $normalizedToken = $this->normalizeToken($token);
        $session = Cache::get(self::CACHE_PREFIX . $normalizedToken);
        if (!$session || $session['status'] !== 'pending') {
            return false;
        }

        $session['status'] = 'confirmed';
        $session['confirmed_at'] = now()->toISOString();
        $authData = [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'is_verified' => (bool) $user->is_verified,
            ],
        ];

        $session['auth_data'] = $authData;
        $session['data'] = $authData;

        // Tasdiqlangan sessiyani frontend olib ketishi uchun 60 soniya saqlaymiz
        Cache::put(self::CACHE_PREFIX . $normalizedToken, $session, 60);

        return true;
    }

    /**
     * Sessiyani bekor qilish
     */
    public function cancelSession(string $token): void
    {
        $normalizedToken = $this->normalizeToken($token);
        $session = Cache::get(self::CACHE_PREFIX . $normalizedToken);
        if ($session) {
            $session['status'] = 'cancelled';
            Cache::put(self::CACHE_PREFIX . $normalizedToken, $session, 30);
        }
    }

    /**
     * Sessiyani rad etish (masalan, Login bo'limidan kirganda raqam ro'yxatdan o'tmagan bo'lsa)
     */
    public function rejectSession(string $token, string $reason = 'not_registered', string $message = ''): void
    {
        $normalizedToken = $this->normalizeToken($token);
        $session = Cache::get(self::CACHE_PREFIX . $normalizedToken);
        if ($session) {
            $session['status'] = $reason;
            $session['message'] = $message;
            Cache::put(self::CACHE_PREFIX . $normalizedToken, $session, 60);
        }
    }

    /**
     * Frontend tomonidan sessiya holatini tekshirish va tasdiqlanganda iste'mol qilish (bir martalik)
     */
    public function checkAndConsumeSession(string $token): array
    {
        $normalizedToken = $this->normalizeToken($token);
        $session = Cache::get(self::CACHE_PREFIX . $normalizedToken);

        if (!$session) {
            return [
                'status' => 'expired',
                'message' => 'Kirish sessiyasining muddati tugagan yoki mavjud emas.',
            ];
        }

        if ($session['status'] === 'confirmed') {
            // Bir martalik xavfsizlik: tokenni keshdan o'chiramiz
            Cache::forget(self::CACHE_PREFIX . $normalizedToken);

            return [
                'status' => 'confirmed',
                'auth_data' => $session['auth_data'] ?? null,
                'data' => $session['auth_data'] ?? null,
            ];
        }

        if ($session['status'] === 'not_registered') {
            Cache::forget(self::CACHE_PREFIX . $normalizedToken);

            return [
                'status' => 'not_registered',
                'message' => $session['message'] ?? "Bu telefon raqami hali ro‘yxatdan o‘tmagan. Iltimos, ro‘yxatdan o‘tish bo‘limidan ro‘yxatdan o‘ting.",
            ];
        }

        if ($session['status'] === 'cancelled') {
            Cache::forget(self::CACHE_PREFIX . $normalizedToken);

            return [
                'status' => 'cancelled',
                'message' => 'Kirish foydalanuvchi tomonidan bekor qilindi.',
            ];
        }

        return [
            'status' => 'pending',
            'expires_at' => $session['expires_at'] ?? null,
        ];
    }
}
