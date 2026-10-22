<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Click;
use App\Models\UserTelegram;
use App\Models\Vacancy;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramKeyboardService;
use App\Services\Telegram\TelegramMessageFormatter;
use App\Services\Telegram\TelegramStateService;

class MyVacanciesHandler
{
    public function __construct(
        protected TelegramBotService $bot,
        protected TelegramKeyboardService $keyboard,
        protected TelegramMessageFormatter $formatter,
        protected TelegramStateService $state
    ) {}

    public function handle(int|string $chatId): void
    {
        // 3 soniyalik spamdan himoya
        if ($this->state->isCoolingDown($chatId, 'my_vacancies', 3)) {
            $this->bot->sendMessage(
                $chatId,
                "⚠️ <b>Tezkor harakat cheklovi</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "⏱ <i>Iltimos, biroz kutib harakat qiling. Ushbu tugmani 3 soniyada faqat 1 marta bosish mumkin.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━"
            );
            return;
        }

        $userTelegram = UserTelegram::where('telegram_id', $chatId)->first();
        if (!$userTelegram || empty($userTelegram->verified_at) || empty($userTelegram->user_id)) {
            $this->bot->sendMessage(
                $chatId,
                "⚠️ <b>Avtorizatsiyadan o‘tmagansiz!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Iltimos, arizalaringiz holatini ko‘rish uchun avval <b>📞 Kirish</b> tugmasi orqali tizimga kiring.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━",
                $this->keyboard->guestMainMenu()
            );
            return;
        }

        try {
            $userId = $userTelegram->user_id;
            $clickVacancyIds = Click::where('user_id', $userId)->pluck('vacancy_id');

            $userVacancies = Vacancy::with([
                'demands.dir_demand',
                'occupation',
                'region',
                'telegClick' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
            ])
            ->whereIn('id', $clickVacancyIds)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

            $text = $this->formatter->formatMyVacancies($userVacancies);

            $this->bot->sendMessage(
                $chatId,
                $text,
                $this->keyboard->userMainMenu()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("MyVacanciesHandler error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->bot->sendMessage(
                $chatId,
                "❌ <b>Arizalarni yuklashda xatolik yuz berdi</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "⚠️ <i>Tizimdan arizalarni yuklashda muammo yuzaga keldi. Iltimos, keyinroq qayta urinib ko'ring.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━"
            );
        }
    }
}
