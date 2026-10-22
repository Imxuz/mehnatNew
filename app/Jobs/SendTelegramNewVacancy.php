<?php

namespace App\Jobs;

use App\Models\UserTelegram;
use App\Models\Vacancy;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramKeyboardService;
use App\Services\Telegram\TelegramMessageFormatter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramNewVacancy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    public int $vacancy_id;

    /**
     * Create a new job instance.
     */
    public function __construct(int $vacancy_id)
    {
        $this->vacancy_id = $vacancy_id;
        $queueName = env('RABBITMQ_QUEUE', 'telegram_news_test_queue');
        if ($queueName) {
            $this->onQueue($queueName);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(
        TelegramBotService $bot,
        TelegramMessageFormatter $formatter,
        TelegramKeyboardService $keyboard
    ): void {
        $vacancy = Vacancy::with([
            'demands.dir_demand',
            'specialDemands.dir_demand',
            'occupation',
            'region',
        ])->find($this->vacancy_id);

        if (!$vacancy) {
            Log::warning("SendTelegramNewVacancy: Vacancy ID={$this->vacancy_id} topilmadi.");
            return;
        }

        $messageText = $formatter->formatNewVacancyBroadcast($vacancy);
        $replyMarkup = $keyboard->vacancyBroadcastMarkup($this->vacancy_id);

        Log::info("SendTelegramNewVacancy: ID={$this->vacancy_id} vakansiya bo‘yicha ommaviy xabarnoma boshlandi.");

        $sentCount = 0;
        $failedCount = 0;
        $blockedCount = 0;

        // Barcha faol telegram foydalanuvchilariga chunk qilib yuborish
        UserTelegram::whereNotNull('telegram_id')
            ->select('id', 'telegram_id')
            ->chunk(100, function ($users) use ($bot, $messageText, $replyMarkup, &$sentCount, &$failedCount, &$blockedCount) {
                foreach ($users as $userTelegram) {
                    $chatId = $userTelegram->telegram_id;

                    $result = $bot->sendMessage(
                        $chatId,
                        $messageText,
                        $replyMarkup
                    );

                    if (!empty($result['ok'])) {
                        $sentCount++;
                    } else {
                        $failedCount++;

                        // Agar foydalanuvchi botni bloklagan bo'lsa
                        if (!empty($result['is_blocked'])) {
                            $blockedCount++;
                            Log::info("Foydalanuvchi botni bloklagan: telegram_id={$chatId}");
                            // Agar kerak bo'lsa, verified_at ni null qilish yoki tozalash
                            $userTelegram->update(['verified_at' => null]);
                        }
                    }

                    // Telegram Rate Limit (Telegram ~30 msg/s gacha ruxsat beradi, xavfsiz tezlik: ~25 msg/s = har bir xabarga 40ms tanaffus)
                    usleep(40000); // 0.04 soniya
                }
            });

        Log::info("SendTelegramNewVacancy: Tugallandi. Yuborildi: {$sentCount}, Xatolar: {$failedCount}, Bloklaganlar: {$blockedCount}");
    }
}
