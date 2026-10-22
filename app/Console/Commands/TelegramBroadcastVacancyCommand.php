<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramNewVacancy;
use App\Models\Vacancy;
use Illuminate\Console\Command;

class TelegramBroadcastVacancyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:broadcast {vacancy_id : Chop etilgan vakansiya ID raqami}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yangi e’lon qilingan vakansiyani Telegram foydalanuvchilariga xabarnoma sifatida yuborish';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $vacancyId = (int) $this->argument('vacancy_id');
        $vacancy = Vacancy::find($vacancyId);

        if (!$vacancy) {
            $this->error("❌ ID={$vacancyId} bo‘lgan vakansiya topilmadi!");
            return self::FAILURE;
        }

        $this->info("📢 ID={$vacancyId} vakansiya bo‘yicha xabarnoma yuborilmoqda...");

        // Jobni darhol sinxron yoki navbatga qo'yish
        SendTelegramNewVacancy::dispatchSync($vacancyId);

        $this->info("✅ Xabarnoma jarayoni yakunlandi!");
        return self::SUCCESS;
    }
}
