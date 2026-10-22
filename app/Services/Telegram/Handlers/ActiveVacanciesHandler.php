<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Vacancy;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramKeyboardService;
use App\Services\Telegram\TelegramMessageFormatter;
use App\Services\Telegram\TelegramStateService;

class ActiveVacanciesHandler
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
        if ($this->state->isCoolingDown($chatId, 'active_vacancies', 3)) {
            $this->bot->sendMessage(
                $chatId,
                "⚠️ <b>Tezkor harakat cheklovi</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "⏱ <i>Iltimos, biroz kutib harakat qiling. Ushbu tugmani 3 soniyada faqat 1 marta bosish mumkin.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━"
            );
            return;
        }

        try {
            $vacancies = Vacancy::with([
                'demands.dir_demand',
                'occupation',
                'region',
            ])
            ->whereNotNull('publication')
            ->where('close_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

            $text = $this->formatter->formatActiveVacancies($vacancies);

            $this->bot->sendMessage(
                $chatId,
                $text,
                $this->keyboard->activeVacanciesMarkup()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ActiveVacanciesHandler error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->bot->sendMessage(
                $chatId,
                "❌ <b>Vakansiyalarni yuklashda xatolik yuz berdi</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "⚠️ <i>Vakansiyalar ro'yxatini yuklash imkoni bo'lmadi. Iltimos, keyinroq qayta urinib ko'ring.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━"
            );
        }
    }
}
