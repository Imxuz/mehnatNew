<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramBotService;
use Illuminate\Console\Command;

class TelegramSetWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook 
                            {action=info : Bajariladigan amal (info, set, delete)}
                            {--url= : Webhook URL manzili (set amali uchun)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Telegram bot Webhook sozlamalarini boshqarish (info, set, delete)';

    /**
     * Execute the console command.
     */
    public function handle(TelegramBotService $bot): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'set':
                $url = $this->option('url') ?: config('telegram.webhook_url');
                if (!$url) {
                    $this->error("❌ Webhook URL ko‘rsatilmadi! Iltimos, --url=... parametrini bering yoki config/telegram.php dagi webhook_url ni to‘ldiring.");
                    return self::FAILURE;
                }

                $secret = config('telegram.webhook_secret');
                $result = $bot->setWebhook($url, $secret);

                if (!empty($result['ok'])) {
                    $this->info("✅ Webhook muvaffaqiyatli o‘rnatildi: {$url}");
                } else {
                    $this->error("❌ Xatolik: " . ($result['description'] ?? 'Noma’lum xato'));
                }
                break;

            case 'delete':
                $result = $bot->deleteWebhook(true);
                if (!empty($result['ok'])) {
                    $this->info("✅ Webhook muvaffaqiyatli o‘chirildi (kutilayotgan barcha xabarlar tozalandi).");
                } else {
                    $this->error("❌ Xatolik: " . ($result['description'] ?? 'Noma’lum xato'));
                }
                break;

            case 'info':
            default:
                $info = $bot->getWebhookInfo();
                $this->info("ℹ️ Telegram Webhook ma'lumotlari:");
                $this->table(['Parametr', 'Qiymat'], [
                    ['URL', $info['result']['url'] ?? '(O‘rnatilmagan)'],
                    ['Custom Certificate', ($info['result']['has_custom_certificate'] ?? false) ? 'Ha' : 'Yo‘q'],
                    ['Pending Updates', $info['result']['pending_update_count'] ?? 0],
                    ['Last Error Date', isset($info['result']['last_error_date']) ? date('Y-m-d H:i:s', $info['result']['last_error_date']) : '-'],
                    ['Last Error Message', $info['result']['last_error_message'] ?? '-'],
                ]);
                break;
        }

        return self::SUCCESS;
    }
}
