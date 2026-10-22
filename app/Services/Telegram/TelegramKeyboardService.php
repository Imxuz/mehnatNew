<?php

namespace App\Services\Telegram;

class TelegramKeyboardService
{
    /**
     * Tizimga kirmagan (Mehmon) foydalanuvchilar uchun bosh menyu
     */
    public function guestMainMenu(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '💼 Faol vakansiyalar'],
                    ['text' => "ℹ️ Ma'lumot"],
                ],
                [
                    ['text' => '🔔 Eslatma'],
                    ['text' => '📞 Kirish'],
                ],
            ],
            'resize_keyboard' => true,
            'is_persistent' => true,
        ];
    }

    /**
     * Tizimga kirgan (Avtorizatsiyadan o'tgan) foydalanuvchilar uchun bosh menyu
     */
    public function userMainMenu(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '💼 Faol vakansiyalar'],
                    ['text' => '📁 Vakansiyalarim'],
                ],
                [
                    ['text' => '🔔 Eslatma'],
                    ['text' => '🚪 Chiqish'],
                ],
            ],
            'resize_keyboard' => true,
            'is_persistent' => true,
        ];
    }

    /**
     * Bekor qilish tugmasi (masalan, maxfiy kod kiritishda)
     */
    public function cancelMenu(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '❌ Bekor qilish'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Telefon raqamini yuborish menyusi
     */
    public function phoneRequestMenu(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '📱 Raqamni yuborish', 'request_contact' => true],
                ],
                [
                    ['text' => '❌ Bekor qilish'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Mavjud profilni tasdiqlash yoki yangi raqam kiritish menyusi
     */
    public function authChoiceMenu(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '✅ Ha, shu profil bilan kirish'],
                ],
                [
                    ['text' => '📱 Boshqa / Yangi raqam bilan kirish'],
                ],
                [
                    ['text' => '❌ Bekor qilish'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Yangi vakansiya e'loni uchun inline tugma
     */
    public function vacancyBroadcastMarkup(int|string $vacancyId): array
    {
        $baseUrl = config('app.telegram_webapp_url') ?: env('TELEGRAM_WEBAPP_URL', 'https://mehnat.ngmk.uz');
        return [
            'inline_keyboard' => [
                [
                    [
                        'text' => "🌐 Batafsil ma'lumot olish",
                        'web_app' => ['url' => "{$baseUrl}/vacancies"],
                    ],
                ],
            ],
        ];
    }

    /**
     * Faol vakansiyalar ro'yxati uchun inline tugma
     */
    public function activeVacanciesMarkup(): array
    {
        $baseUrl = config('app.telegram_webapp_url') ?: env('TELEGRAM_WEBAPP_URL', 'https://mehnat.ngmk.uz');
        return [
            'inline_keyboard' => [
                [
                    [
                        'text' => "🌐 Saytda ko'rish",
                        'web_app' => ['url' => "{$baseUrl}/vacancies"],
                    ],
                ],
            ],
        ];
    }
}

