<?php

namespace App\Services\Telegram\Handlers;

use App\Models\UserTelegram;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramKeyboardService;
use App\Services\Telegram\TelegramMessageFormatter;
use App\Services\Telegram\TelegramStateService;

class StartCommandHandler
{
    public function __construct(
        protected TelegramBotService $bot,
        protected TelegramKeyboardService $keyboard,
        protected TelegramMessageFormatter $formatter,
        protected TelegramStateService $state
    ) {}

    public function handle(int|string $chatId, array $from): void
    {
        // Har qanday eski FSM holatini tozalaymiz
        $this->state->clearState($chatId);

        // Foydalanuvchini user_telegram jadvalida ro'yxatdan o'tkazish
        $userTelegram = UserTelegram::firstOrCreate(
            ['telegram_id' => $chatId]
        );

        $isLoggedIn = !empty($userTelegram->verified_at);
        $fullName = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));

        $welcomeText = $this->formatter->welcomeMessage($isLoggedIn, $fullName ?: null);
        $menu = $isLoggedIn ? $this->keyboard->userMainMenu() : $this->keyboard->guestMainMenu();

        $this->bot->sendMessage($chatId, $welcomeText, $menu);
    }
}
