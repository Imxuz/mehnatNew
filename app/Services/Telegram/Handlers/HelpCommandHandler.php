<?php

namespace App\Services\Telegram\Handlers;

use App\Models\Admin;
use App\Models\Vacancy;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramMessageFormatter;

class HelpCommandHandler
{
    public function __construct(
        protected TelegramBotService $bot,
        protected TelegramMessageFormatter $formatter
    ) {}

    /**
     * /help buyrug'i
     */
    public function handleHelp(int|string $chatId): void
    {
        try {
            $admins = Admin::pluck('surname')->filter()->values()->all();
        } catch (\Exception $e) {
            $admins = [];
        }

        $text = $this->formatter->helpMessage($admins);
        $this->bot->sendMessage($chatId, $text);
    }

    /**
     * "Biz haqimizda" yoki "Mavjud vakansiyalar"
     */
    public function handleAbout(int|string $chatId): void
    {
        try {
            $vacancies = Vacancy::with('occupation')
                ->whereNotNull('publication')
                ->where('close_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            if ($vacancies->isEmpty()) {
                $this->bot->sendMessage(
                    $chatId,
                    "⚠️ <b>Ma'lumot topilmadi</b>\n\n"
                    . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                    . "🏢 <i>Hozirda faol bo'sh ish o'rinlari mavjud emas.</i>\n"
                    . "━━━━━━━━━━━━━━━━━━━━━━━━"
                );
                return;
            }

            $names = [];
            foreach ($vacancies as $v) {
                $title = $this->formatter->parseLang($v->occupation->occupation ?? ($v->title_uz ?? ''));
                if ($title) {
                    $names[] = "  • <i>{$title}</i>";
                }
            }

            $header = "🏢 <b>NKMK AJ Bo'sh ish o'rinlari portali</b>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "🔗 <i>Rasmiy veb-sayt:</i> <a href='https://mehnat.ngmk.uz'>mehnat.ngmk.uz</a>\n\n"
                . "📋 <b>Hozirda mavjud bo'sh ish o'rinlari ro'yxati (Top 50):</b>\n\n";

            $body = implode("\n", $names);
            $this->bot->sendMessage($chatId, $header . $body);
        } catch (\Exception $e) {
            $this->bot->sendMessage($chatId, "⚠️ Ma'lumotlarni yuklashda xatolik yuz berdi.");
        }
    }
}
