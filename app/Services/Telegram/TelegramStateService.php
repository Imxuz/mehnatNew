<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Cache;

class TelegramStateService
{
    /**
     * Foydalanuvchi joriy FSM holatini olish
     */
    public function getState(int|string $chatId): ?array
    {
        return Cache::get("telegram_state:{$chatId}");
    }

    /**
     * Foydalanuvchiga yangi FSM holati va ma'lumotlarini o'rnatish
     */
    public function setState(int|string $chatId, string $state, array $data = [], int $ttlMinutes = 30): void
    {
        $payload = array_merge($data, [
            'state' => $state,
            'updated_at' => now()->toIso8601String(),
        ]);

        Cache::put("telegram_state:{$chatId}", $payload, now()->addMinutes($ttlMinutes));
    }

    /**
     * Holat ma'lumotlarini qisman yangilash
     */
    public function updateStateData(int|string $chatId, array $data, int $ttlMinutes = 30): void
    {
        $current = $this->getState($chatId) ?? [];
        $merged = array_merge($current, $data);
        Cache::put("telegram_state:{$chatId}", $merged, now()->addMinutes($ttlMinutes));
    }

    /**
     * Foydalanuvchi holatini tozalash
     */
    public function clearState(int|string $chatId): void
    {
        Cache::forget("telegram_state:{$chatId}");
    }

    /**
     * Tugmalarni ketma-ket bosishdan himoya (Anti-spam / Debounce)
     * Agar foydalanuvchi belgilangan soniyadan tez bossa, true qaytaradi (ya'ni bloklanadi).
     */
    public function isCoolingDown(int|string $chatId, string $action, int $cooldownSeconds = 3): bool
    {
        $cacheKey = "telegram_cooldown:{$chatId}:{$action}";
        if (Cache::has($cacheKey)) {
            return true;
        }

        Cache::put($cacheKey, true, now()->addSeconds($cooldownSeconds));
        return false;
    }

    /**
     * Telegram tomonidan berilgan rasm file_id ni keshga saqlash
     */
    public function getCachedPhotoId(string $key): ?string
    {
        return Cache::get("telegram_photo:{$key}");
    }

    public function setCachedPhotoId(string $key, string $photoId): void
    {
        Cache::put("telegram_photo:{$key}", $photoId, now()->addDays(30));
    }
}
