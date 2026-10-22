<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramUpdateRouter;
use Illuminate\Console\Command;

class TelegramPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll {--timeout=10 : Polling so‘rovining kutish vaqti (soniya)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Telegram botni lokal tekshirish va ishlatish uchun Long-Polling rejimida ishga tushirish';

    /**
     * Execute the console command.
     */
    public function handle(TelegramBotService $bot, TelegramUpdateRouter $router): int
    {
        $this->info("🤖 Mehnat Telegram Bot (Long Polling) ishga tushmoqda...");

        // Bot ma'lumotlarini tekshiramiz
        $me = $bot->getMe();
        if (empty($me['ok'])) {
            $this->error("❌ Bot token noto‘g‘ri yoki Telegram API bilan bog‘lanib bo‘lmadi!");
            if (!empty($me['description'])) {
                $this->error("Xatolik: " . $me['description']);
            }
            return self::FAILURE;
        }

        $botUsername = $me['result']['username'] ?? 'Unknown';
        $this->info("✅ Bot muvaffaqiyatli ulandi: @{$botUsername}");

        // Agar webhook o'rnatilgan bo'lsa, polling ishlashi uchun uni vaqtincha tozalash taklifi
        $webhookInfo = $bot->getWebhookInfo();
        if (!empty($webhookInfo['result']['url'])) {
            $this->warn("⚠️ Diqqat: Webhook o'rnatilgan (" . $webhookInfo['result']['url'] . "). Polling ishlashi uchun uni o'chiryapman...");
            $bot->deleteWebhook();
            $this->info("🧹 Webhook vaqtincha o'chirildi.");
        }

        $this->info("🚀 Xabarlar kutilmoqda... (To‘xtatish uchun Ctrl+C bosing)");

        $offset = 0;
        $timeout = (int) $this->option('timeout');

        while (true) {
            try {
                $updates = $bot->getUpdates($offset, 100, $timeout);

                foreach ($updates as $update) {
                    $updateId = $update['update_id'];
                    $offset = $updateId + 1;

                    $messageText = $update['message']['text'] ?? '[Media/Action]';
                    $fromUser = $update['message']['from']['first_name'] ?? 'Unknown';
                    $chatId = $update['message']['chat']['id'] ?? 'Unknown';

                    $this->line("📩 [{$chatId}] {$fromUser}: {$messageText}");

                    // Routerga uzatish
                    $router->route($update);
                }
            } catch (\Exception $e) {
                $this->error("Xatolik: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Telegram Poll Xatolik: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
                sleep(2);
            }
        }

        return self::SUCCESS;
    }
}
