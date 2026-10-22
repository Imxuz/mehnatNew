<?php

namespace App\Services\Telegram\Handlers;

use App\Models\User;
use App\Models\UserTelegram;
use App\Services\PasswordGeneratorService;
use App\Services\Telegram\TelegramAuthSessionService;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramKeyboardService;
use App\Services\Telegram\TelegramStateService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TelegramDeepLinkAuthHandler
{
    public function __construct(
        protected TelegramBotService $bot,
        protected TelegramKeyboardService $keyboard,
        protected TelegramStateService $state,
        protected TelegramAuthSessionService $sessionService,
        protected PasswordGeneratorService $passwordGenerator
    ) {}

    /**
     * /start auth_{token} deep linkini qayta ishlash
     */
    public function handleDeepLink(int|string $chatId, array $from, string $authToken): void
    {
        $session = $this->sessionService->getSession($authToken);

        if (!$session || $session['status'] !== 'pending') {
            $this->bot->sendMessage(
                $chatId,
                "⚠️ <b>Kirish sessiyasining muddati tugagan</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Iltimos, veb-saytga qaytib, «Telegram orqali kirish» tugmasini qaytadan bosing.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━",
                $this->keyboard->guestMainMenu()
            );
            return;
        }

        $mode = $session['mode'] ?? 'login';

        // Foydalanuvchi allaqachon botimizda ro'yxatdan o'tgan yoki bog'langanmi?
        $userTelegram = UserTelegram::where('telegram_id', $chatId)
            ->whereNotNull('user_id')
            ->whereNotNull('verified_at')
            ->first();

        if ($userTelegram && $userTelegram->user_id) {
            $user = User::find($userTelegram->user_id);
            if ($user) {
                // Holatni saqlab qo'yamiz (foydalanuvchi tugma bosishi yoki yangi raqam yozishi mumkin)
                $this->state->setState($chatId, 'awaiting_auth_choice_or_phone', [
                    'auth_token' => $authToken,
                    'user_id' => $user->id,
                    'mode' => $mode,
                ]);

                // 3 ta tanlov beramiz
                $replyMarkup = [
                    'inline_keyboard' => [
                        [
                            ['text' => '✅ Ha, shu profil bilan kirish', 'callback_data' => "auth_confirm:{$authToken}"],
                        ],
                        [
                            ['text' => '📱 Boshqa / Yangi raqam bilan kirish', 'callback_data' => "auth_new_phone:{$authToken}"],
                        ],
                        [
                            ['text' => '❌ Bekor qilish', 'callback_data' => "auth_cancel:{$authToken}"],
                        ],
                    ],
                ];

                $text = "🏢 <b>NKMK Bo‘sh ish o‘rinlari portali</b>\n\n"
                    . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                    . "👤 <b>Mavjud profil:</b> {$user->name}\n"
                    . "📱 <b>Telefon:</b> +{$user->phone}\n\n"
                    . "🌐 <i>Saytga ushbu profilingiz orqali kirishni tasdiqlaysizmi?</i>\n\n"
                    . "💡 <i>Boshqa telefon raqami bilan kirish yoki yangi hisob ochish uchun pastdagi <b>«📱 Boshqa / Yangi raqam bilan kirish»</b> tugmasini bosing yoki yangi raqamingizni yozib yuboring.</i>\n"
                    . "━━━━━━━━━━━━━━━━━━━━━━━━";

                $this->bot->sendMessage($chatId, $text, $replyMarkup);
                $this->bot->sendMessage($chatId, "👇 <i>Quyidagi tugmalardan birini tanlang:</i>", $this->keyboard->authChoiceMenu());
                return;
            }
        }

        // Yangi yoki bog'lanmagan foydalanuvchi: telefon raqamini so'raymiz
        $this->state->setState($chatId, 'awaiting_auth_contact', [
            'auth_token' => $authToken,
            'mode' => $mode,
        ]);

        $fullName = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')) ?: 'Nomzod';
        $actionDesc = ($mode === 'register') ? "tezkor ro‘yxatdan o‘tish" : "bir zumda kirish";

        $text = "👋 <b>Assalomu alaykum, {$fullName}!</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 <b>NKMK Bo‘sh ish o‘rinlari portali</b>\n\n"
            . "Tizimda {$actionDesc} uchun, iltimos, quyidagi <b>«📱 Raqamni yuborish»</b> tugmasini bosing yoki raqamingizni yozib yuboring:\n\n"
            . "➡️ Namunadagidek: <code>+998901234567</code>\n\n"
            . "📋 <i>«Raqamni yuborish» orqali siz shaxsiy ma’lumotlaringizni qayta ishlashga va foydalanish shartlariga to‘liq rozilik bildirasiz.</i>\n\n"
            . "🇺🇿 <i>Eslatma: Faqat O‘zbekiston telefon raqamlari (+998...) qabul qilinadi.</i>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $this->bot->sendMessage($chatId, $text, $this->keyboard->phoneRequestMenu());
    }

    /**
     * Ulashilgan Kontaktni (vCard) qayta ishlash
     */
    public function processContact(int|string $chatId, array $from, array $contact, string $authToken): void
    {
        // Xavfsizlik tekshiruvi: faqat o'zining shaxsiy SIM-karta kontaktini qabul qilamiz
        if (!empty($contact['user_id']) && (int) $contact['user_id'] !== (int) ($from['id'] ?? 0)) {
            $this->bot->sendMessage(
                $chatId,
                "⚠️ <b>Xavfsizlik xatoligi:</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Iltimos, o‘zingizning Telegram profilingizga biriktirilgan telefon raqamini <b>«📱 Raqamni yuborish»</b> tugmasi orqali yuboring.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━",
                $this->keyboard->phoneRequestMenu()
            );
            return;
        }

        $rawPhone = (string) ($contact['phone_number'] ?? '');
        $this->processPhone($chatId, $from, $rawPhone, $authToken);
    }

    /**
     * Telefon raqami kelganda foydalanuvchini yaratish yoki tizimga kiritish
     */
    public function processPhone(int|string $chatId, array $from, string $rawPhone, string $authToken): void
    {
        $session = $this->sessionService->getSession($authToken);
        if (!$session || $session['status'] !== 'pending') {
            $this->state->clearState($chatId);
            $this->bot->sendMessage(
                $chatId,
                "⚠️ <b>Kirish sessiyasining muddati tugagan</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Iltimos, veb-saytga qaytib, qaytadan urinib ko'ring.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━",
                $this->keyboard->guestMainMenu()
            );
            return;
        }

        $formattedPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (strlen($formattedPhone) === 9) {
            $formattedPhone = '998' . $formattedPhone;
        } elseif (strlen($formattedPhone) === 10 && str_starts_with($formattedPhone, '8')) {
            $formattedPhone = '998' . substr($formattedPhone, 1);
        }

        // 1. Faqat O'zbekiston raqamlari ekanligini qat'iy tekshirish
        if (!str_starts_with($formattedPhone, '998')) {
            $this->bot->sendMessage(
                $chatId,
                "⚠️ <b>Faqat O‘zbekiston telefon raqamlari qabul qilinadi!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "🏢 <b>NKMK Bo‘sh ish o‘rinlari portali</b>\n\n"
                . "Telegram orqali ro‘yxatdan o‘tish va tizimga kirish faqat O‘zbekiston Respublikasi aloqa operatorlariga tegishli (<b>+998...</b>) raqamlar orqali amalga oshiriladi.\n\n"
                . "❌ Xorijiy davlatlar raqamlari orqali ro‘yxatdan o‘tish imkoniyati mavjud emas.\n\n"
                . "Iltimos, O‘zbekiston raqami ulangan Telegram akkauntingizdan foydalaning yoki <b>«📱 Raqamni yuborish»</b> tugmasini bosing.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━",
                $this->keyboard->phoneRequestMenu()
            );
            return;
        }

        // 2. 998 dan keyin aynan 9 ta raqam bo'lishi kerak (jami 12 ta raqam)
        if (!preg_match('/^998[0-9]{9}$/', $formattedPhone)) {
            $this->bot->sendMessage(
                $chatId,
                "❌ <b>Telefon raqami noto‘g‘ri formatda!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Iltimos, <b>«📱 Raqamni yuborish»</b> tugmasini bosing yoki namunadagidek kiriting:\n\n"
                . "➡️ <code>+998901234567</code>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━",
                $this->keyboard->phoneRequestMenu()
            );
            return;
        }

        $mode = $session['mode'] ?? 'login';
        $user = User::where('phone', $formattedPhone)->first();
        $isNewUser = false;
        $plainPassword = null;

        // 1. Agar «Kirish» (login) rejimi bo'lsa va raqam bazada bo'lmasa:
        if ($mode === 'login' && !$user) {
            $this->sessionService->rejectSession(
                $authToken,
                'not_registered',
                "Bu telefon raqami (+{$formattedPhone}) hali ro‘yxatdan o‘tmagan."
            );
            $this->state->clearState($chatId);

            $msg = "❌ <b>Ushbu telefon raqami hali ro‘yxatdan o‘tmagan!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "📱 <b>Telefon:</b> <code>+{$formattedPhone}</code>\n\n"
                . "Siz saytga <b>«Kirish»</b> bo‘limi orqali kirmoqchi bo‘ldingiz, ammo bu raqam tizimda ro‘yxatdan o‘tmagan.\n\n"
                . "👉 <i>Iltimos, saytning <b>«Ro‘yxatdan o‘tish»</b> bo‘limi orqali yangi hisob yarating yoki ro‘yxatdan o‘tgan boshqa raqamingizni kiriting.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";

            $this->bot->sendMessage($chatId, $msg, $this->keyboard->guestMainMenu());
            return;
        }

        // 2. Agar «Ro'yxatdan o'tish» (register) rejimi bo'lsa va foydalanuvchi bazada bo'lmasa:
        if (!$user) {
            // Yangi foydalanuvchi yaratamiz va xavfsiz parol generatsiya qilamiz
            $plainPassword = $this->passwordGenerator->generate(10, readable: true);
            $fullName = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')) ?: 'Nomzod';

            $user = User::create([
                'phone' => $formattedPhone,
                'name' => $fullName,
                'password' => Hash::make($plainPassword),
                'is_verified' => true,
                'agreement' => 1,
            ]);

            $isNewUser = true;
        } else {
            // Agar avval yaratilgan, lekin tasdiqlanmagan bo'lsa
            if (!$user->is_verified) {
                $user->update(['is_verified' => true]);
            }
        }

        // Telegram profilni xavfsiz bog'laymiz
        // 1. Agar ushbu user_id boshqa bir telegram_id ga bog'langan bo'lsa, tozalaymiz
        UserTelegram::where('user_id', $user->id)
            ->where('telegram_id', '!=', $chatId)
            ->update(['user_id' => null, 'verified_at' => null]);

        // 2. Ushbu telegram_id bo'yicha updateOrCreate qilamiz (chunki telegram_id UNIQUE)
        UserTelegram::updateOrCreate(
            ['telegram_id' => $chatId],
            [
                'user_id' => $user->id,
                'verified_at' => now(),
                'sms_code' => null,
                'expired_at' => null,
            ]
        );

        // JWT tokenlar generatsiyasi
        $accessToken = auth('api')->login($user);
        $refreshToken = auth('api')->claims(['refresh' => true])->setTTL(60 * 24 * 30)->login($user);

        // Sessiyani tasdiqlaymiz (veb-sayt darhol kirib ketishi uchun)
        $this->sessionService->confirmSession($authToken, $user, $accessToken, $refreshToken);
        $this->state->clearState($chatId);

        if ($isNewUser) {
            $msg = "🎉 <b>Tabriklaymiz! Siz «NKMK Bo‘sh ish o‘rinlari portali»da muvaffaqiyatli ro‘yxatdan o‘tdingiz!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "📱 <b>Telefon raqamingiz:</b> <code>+{$formattedPhone}</code>\n"
                . "🔑 <b>Saytga kirish parolingiz:</b> <code>{$plainPassword}</code>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
                . "🌐 <b>Brauzeringizga qaytishingiz mumkin — shaxsiy kabinetingiz ochildi!</b>\n\n"
                . "💡 <i>Eslatma: Ushbu parolni saqlab qoling. Keyingi safar saytga Telegram orqali bitta tugma bilan yoki ushbu parol yordamida kirishingiz mumkin.</i>";
        } else {
            $msg = "✅ <b>Muvaffaqiyatli kirdingiz!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "👤 <b>{$user->name}</b>\n"
                . "📱 <b>+{$formattedPhone}</b>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
                . "🌐 <b>Brauzeringizga qaytishingiz mumkin — shaxsiy kabinetingiz ochildi!</b>";
        }

        $this->bot->sendMessage($chatId, $msg, $this->keyboard->userMainMenu());
    }

    /**
     * Mavjud profil bilan kirishni tasdiqlash
     */
    public function confirmCurrentProfile(int|string $chatId, string $authToken): void
    {
        $session = $this->sessionService->getSession($authToken);
        if (!$session || $session['status'] !== 'pending') {
            $this->bot->sendMessage($chatId, "⚠️ Ushbu sessiyaning muddati tugagan. Iltimos, saytdan qayta urinib ko‘ring.", $this->keyboard->userMainMenu());
            return;
        }

        $userTelegram = UserTelegram::where('telegram_id', $chatId)->whereNotNull('user_id')->first();
        $userId = $userTelegram?->user_id;
        if (!$userId) {
            $currentState = $this->state->getState($chatId);
            $userId = $currentState['user_id'] ?? null;
        }
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            $this->bot->sendMessage($chatId, "⚠️ Foydalanuvchi ma'lumotlari topilmadi.", $this->keyboard->guestMainMenu());
            return;
        }

        // JWT tokenlar
        $accessToken = auth('api')->login($user);
        $refreshToken = auth('api')->claims(['refresh' => true])->setTTL(60 * 24 * 30)->login($user);

        $this->sessionService->confirmSession($authToken, $user, $accessToken, $refreshToken);
        $this->state->clearState($chatId);

        $this->bot->sendMessage(
            $chatId,
            "✅ <b>Kirish tasdiqlandi!</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>{$user->name}</b>\n"
            . "📱 <b>+{$user->phone}</b>\n\n"
            . "🌐 Brauzeringizga qaytishingiz mumkin — shaxsiy kabinetingiz ochildi!\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━",
            $this->keyboard->userMainMenu()
        );
    }

    /**
     * Boshqa raqam kiritishni so'rash
     */
    public function askNewPhone(int|string $chatId, string $authToken): void
    {
        $session = $this->sessionService->getSession($authToken);
        if (!$session || $session['status'] !== 'pending') {
            $this->bot->sendMessage($chatId, "⚠️ Ushbu sessiyaning muddati tugagan. Iltimos, saytdan qayta urinib ko‘ring.", $this->keyboard->userMainMenu());
            return;
        }

        $mode = $session['mode'] ?? 'login';
        $this->state->setState($chatId, 'awaiting_auth_contact', [
            'auth_token' => $authToken,
            'mode' => $mode,
        ]);

        $text = "📱 <b>Boshqa telefon raqami orqali kirish:</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "Iltimos, pastdagi <b>«📱 Raqamni yuborish»</b> tugmasini bosing yoki telefon raqamingizni quyidagi formatda yozib yuboring:\n\n"
            . "➡️ Masalan: <code>+998901234567</code>\n\n"
            . "📋 <i>Raqam kiritish orqali siz shaxsiy ma’lumotlaringizni qayta ishlashga to‘liq rozilik bildirasiz.</i>\n\n"
            . "🇺🇿 <i>Eslatma: Faqat O‘zbekiston telefon raqamlari (+998...) qabul qilinadi.</i>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        $this->bot->sendMessage($chatId, $text, $this->keyboard->phoneRequestMenu());
    }

    /**
     * Inline tugmalar bosilganda (CallbackQuery)
     */
    public function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $data = $callbackQuery['data'] ?? '';
        $callbackId = $callbackQuery['id'] ?? null;

        if (!$chatId || !$callbackId) {
            return;
        }

        $this->bot->answerCallbackQuery($callbackId);

        if (str_starts_with($data, 'auth_confirm:')) {
            $authToken = str_replace('auth_confirm:', '', $data);
            $this->confirmCurrentProfile($chatId, $authToken);
            return;
        }

        if (str_starts_with($data, 'auth_new_phone:')) {
            $authToken = str_replace('auth_new_phone:', '', $data);
            $this->askNewPhone($chatId, $authToken);
            return;
        }

        if (str_starts_with($data, 'auth_cancel:')) {
            $authToken = str_replace('auth_cancel:', '', $data);
            $this->sessionService->cancelSession($authToken);
            $this->state->clearState($chatId);

            $this->bot->sendMessage(
                $chatId,
                "↩️ <b>Kirish amaliyoti bekor qilindi.</b>",
                $this->keyboard->userMainMenu()
            );
        }
    }
}
