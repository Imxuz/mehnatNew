<?php

namespace App\Services\Telegram\Handlers;

use App\Models\User;
use App\Models\UserTelegram;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramKeyboardService;
use App\Services\Telegram\TelegramStateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthHandler
{
    public function __construct(
        protected TelegramBotService $bot,
        protected TelegramKeyboardService $keyboard,
        protected TelegramStateService $state
    ) {}

    /**
     * Kirish jarayonini boshlash
     */
    public function startLogin(int|string $chatId): void
    {
        $this->state->setState($chatId, 'awaiting_phone');

        $text = "🔑 <b>Tizimga kirish</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "📱 <i>Iltimos, tizimda ro'yxatdan o'tgan telefon raqamingizni quyidagi tugma orqali yuboring yoki namunadagidek kiriting:</i>\n\n"
            . "➡️ <code>+998991234567</code>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $this->bot->sendMessage($chatId, $text, $this->keyboard->phoneRequestMenu());
    }

    /**
     * Telefon raqamini qabul qilish va tekshirish
     */
    public function processPhone(int|string $chatId, string $rawPhone): void
    {
        $formattedPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (strlen($formattedPhone) === 9) {
            $formattedPhone = '998' . $formattedPhone;
        }

        if (!str_starts_with($formattedPhone, '998')) {
            $text = "⚠️ <b>Faqat O‘zbekiston telefon raqamlari qabul qilinadi!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Telegram orqali kirish faqat O‘zbekiston Respublikasi aloqa operatorlariga tegishli (<b>+998...</b>) raqamlar orqali amalga oshiriladi.\n\n"
                . "❌ Xorijiy davlatlar raqamlari orqali kirish imkoniyati mavjud emas.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
            $this->bot->sendMessage($chatId, $text, $this->keyboard->phoneRequestMenu());
            return;
        }

        if (!preg_match('/^998[0-9]{9}$/', $formattedPhone)) {
            $text = "❌ <b>Telefon raqami noto‘g‘ri formatda!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Iltimos, raqamni quyidagi namunadagidek kiriting yoki <b>📱 Raqamni yuborish</b> tugmasini bosing:\n\n"
                . "➡️ <code>+998901234567</code>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
            $this->bot->sendMessage($chatId, $text, $this->keyboard->phoneRequestMenu());
            return;
        }

        $this->state->setState($chatId, 'awaiting_code', ['phone' => $formattedPhone]);

        $text = "📲 <b>Telefon raqami qabul qilindi</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "🔐 <i>Endi mehnat.ngmk.uz saytidagi shaxsiy kabinetingizdan olingan maxfiy kodni kiriting.</i>\n\n"
            . "⚠️ <b>Muhim eslatma:</b> <i>Maxfiy kod shaxsiy kabinetingizdagi Telegram bo'limida ko'rsatiladi.</i>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $this->bot->sendMessage($chatId, $text, $this->keyboard->cancelMenu());
    }

    /**
     * Shaxsiy kabinetdan olingan maxfiy kodni tekshirish
     */
    public function processSecretCode(int|string $chatId, string $code, array $stateData): void
    {
        $phone = $stateData['phone'] ?? null;
        $inputCode = trim($code);

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $text = "❌ <b>Kirishda xatolik yuz berdi</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "⚠️ <b>Tafsilot:</b> <i>Ushbu raqamli foydalanuvchi topilmadi. Iltimos, raqamni to'g'ri formatda yuboring (+998XXXXXXXXX).</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
            $this->bot->sendMessage($chatId, $text, $this->keyboard->cancelMenu());
            $this->state->setState($chatId, 'awaiting_phone');
            return;
        }

        $userTelegram = UserTelegram::where('user_id', $user->id)->first();
        if (!$userTelegram) {
            $text = "❌ <b>Kirishda xatolik yuz berdi</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "⚠️ <b>Tafsilot:</b> <i>Sizga tizimga kirish uchun ruxsat berilmagan (Telegram ma'lumotlari topilmadi).</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
            $this->bot->sendMessage($chatId, $text, $this->keyboard->guestMainMenu());
            $this->state->clearState($chatId);
            return;
        }

        if ($userTelegram->expired_at && $userTelegram->expired_at < now()) {
            $text = "❌ <b>Kod muddati tugagan</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "⚠️ <b>Tafsilot:</b> <i>Siz mehnat.ngmk.uz portalidan parolni qayta oling.\n❗️Parol eskirgan yoki muddati o‘tgan bo‘lishi mumkin.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
            $this->bot->sendMessage($chatId, $text, $this->keyboard->guestMainMenu());
            $this->state->clearState($chatId);
            return;
        }

        if ($userTelegram->sms_code !== $inputCode) {
            $text = "❌ <b>Maxfiy kod xato!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "🔐 <i>Iltimos, maxfiy kodni qaytadan tekshirib kiriting.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
            $this->bot->sendMessage($chatId, $text, $this->keyboard->cancelMenu());
            return;
        }

        try {
            DB::beginTransaction();

            // Agar oldin bu telegram_id boshqa qatorda bo'lsa (yoki user_id siz bo'lsa), tozalaymiz
            UserTelegram::where('telegram_id', $chatId)
                ->where('id', '!=', $userTelegram->id)
                ->delete();

            $userTelegram->update([
                'telegram_id' => $chatId,
                'verified_at' => now(),
                'sms_code' => null,
                'expired_at' => null,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Telegram foydalanuvchini yangilashda xatolik: " . $e->getMessage());

            $this->bot->sendMessage($chatId, "⚠️ Tizimda xatolik yuz berdi. Iltimos, keyinroq qayta urinib ko'ring.", $this->keyboard->guestMainMenu());
            $this->state->clearState($chatId);
            return;
        }

        $this->state->clearState($chatId);

        $text = "🎉 <b>Muvaffaqiyatli kirish!</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <i>Tizimga muvaffaqiyatli kirdingiz. Sizning profilingiz Telegram bot bilan muvaffaqiyatli bog'landi.</i>\n"
            . "📱 <i>Bosh menyuning barcha imkoniyatlari faollashtirildi.</i>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $this->bot->sendMessage($chatId, $text, $this->keyboard->userMainMenu());
    }

    /**
     * Tizimdan chiqish (Logout)
     */
    public function handleLogout(int|string $chatId): void
    {
        $userTelegram = UserTelegram::where('telegram_id', $chatId)->first();
        if ($userTelegram) {
            $userTelegram->update([
                'user_id' => null,
                'verified_at' => null,
                'sms_code' => null,
                'expired_at' => null,
            ]);
        }

        $this->state->clearState($chatId);

        $text = "🚪 <b>Tizimdan chiqish</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "✅ <i>Siz tizimdan muvaffaqiyatli chiqdingiz.</i>\n"
            . "↩️ <i>Mehmon menyusiga qaytildi.</i>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $this->bot->sendMessage($chatId, $text, $this->keyboard->guestMainMenu());
    }

    /**
     * Jarayonni bekor qilish
     */
    public function cancel(int|string $chatId): void
    {
        $this->state->clearState($chatId);

        $userTelegram = UserTelegram::where('telegram_id', $chatId)->first();
        $isLoggedIn = $userTelegram && !empty($userTelegram->verified_at);
        $menu = $isLoggedIn ? $this->keyboard->userMainMenu() : $this->keyboard->guestMainMenu();

        $this->bot->sendMessage($chatId, "↩️ Amaliyot bekor qilindi. Bosh menyudasiz.", $menu);
    }
}
