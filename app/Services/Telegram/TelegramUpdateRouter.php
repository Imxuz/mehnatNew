<?php

namespace App\Services\Telegram;

use App\Models\UserTelegram;
use App\Services\Telegram\Handlers\ActiveVacanciesHandler;
use App\Services\Telegram\Handlers\AuthHandler;
use App\Services\Telegram\Handlers\HelpCommandHandler;
use App\Services\Telegram\Handlers\MyVacanciesHandler;
use App\Services\Telegram\Handlers\StartCommandHandler;
use App\Services\Telegram\Handlers\StaticInfoHandler;
use App\Services\Telegram\Handlers\TelegramDeepLinkAuthHandler;
use Illuminate\Support\Facades\Log;

class TelegramUpdateRouter
{
    public function __construct(
        protected TelegramBotService $bot,
        protected TelegramKeyboardService $keyboard,
        protected TelegramStateService $state,
        protected StartCommandHandler $startHandler,
        protected AuthHandler $authHandler,
        protected ActiveVacanciesHandler $activeVacanciesHandler,
        protected MyVacanciesHandler $myVacanciesHandler,
        protected StaticInfoHandler $staticInfoHandler,
        protected HelpCommandHandler $helpHandler,
        protected TelegramDeepLinkAuthHandler $deepLinkAuthHandler
    ) {}

    /**
     * Telegram Update obyektini tahlil qilish va kerakli handlerga yo'naltirish
     */
    public function route(array $update): void
    {
        // 0. Inline tugmalar (Callback Query) tekshiruvi
        if (!empty($update['callback_query'])) {
            $this->deepLinkAuthHandler->handleCallbackQuery($update['callback_query']);
            return;
        }

        $message = $update['message'] ?? null;
        if (!$message) {
            return;
        }

        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');
        $from = $message['from'] ?? [];

        if (!$chatId) {
            return;
        }

        $contact = $message['contact'] ?? null;
        if ($contact && !empty($contact['phone_number'])) {
            $text = (string) $contact['phone_number'];
        }

        // 1. Bekor qilish buyrug'i tekshiruvi
        if ($text === '❌ Bekor qilish' || $text === '/cancel') {
            $this->authHandler->cancel($chatId);
            return;
        }

        // 1.5. Deep-link orqali kirish (/start auth_{token})
        if (str_starts_with($text, '/start ') && str_contains($text, 'auth_')) {
            $this->state->clearState($chatId);
            $payload = trim(substr($text, 7));
            $authToken = explode(' ', $payload)[0];
            $this->deepLinkAuthHandler->handleDeepLink($chatId, $from, $authToken);
            return;
        }

        // 2. Asosiy menyu tugmalari bosilganda, eski holatni avtomatik tozalaymiz
        $isMenuCommand = in_array($text, [
            '/start',
            '/help',
            '💼 Faol vakansiyalar',
            '📁 Vakansiyalarim',
            '🔔 Eslatma',
            "ℹ️ Ma'lumot",
            "ℹ️ Saytga tashrif",
            '📞 Kontaktlar',
            '📞 Kirish',
            '🔑 Kirish',
            '🚪 Chiqish',
            'Biz haqimizda',
            'Mavjud vakansiyalar',
        ], true);

        // 3. FSM (Conversation State) tekshiruvi (faqat menyu buyrug'i bo'lmaganda)
        if (!$isMenuCommand) {
            $currentState = $this->state->getState($chatId);
            if ($currentState && !empty($currentState['state'])) {
                switch ($currentState['state']) {
                    case 'awaiting_auth_choice_or_phone':
                        $authToken = $currentState['auth_token'] ?? '';
                        $lowerText = mb_strtolower(trim($text));

                        if ($text === '✅ Ha, shu profil bilan kirish' || $lowerText === 'ha' || str_contains($lowerText, 'tasdiq')) {
                            $this->deepLinkAuthHandler->confirmCurrentProfile($chatId, $authToken);
                            return;
                        }

                        if ($text === '📱 Boshqa / Yangi raqam bilan kirish' || str_contains($lowerText, 'boshqa') || str_contains($lowerText, 'yangi')) {
                            $this->deepLinkAuthHandler->askNewPhone($chatId, $authToken);
                            return;
                        }

                        $contact = $message['contact'] ?? null;
                        if ($contact) {
                            $this->deepLinkAuthHandler->processContact($chatId, $from, $contact, $authToken);
                        } else {
                            $this->deepLinkAuthHandler->processPhone($chatId, $from, $text, $authToken);
                        }
                        return;

                    case 'awaiting_auth_contact':
                        $authToken = $currentState['auth_token'] ?? '';
                        $contact = $message['contact'] ?? null;
                        if ($contact) {
                            $this->deepLinkAuthHandler->processContact($chatId, $from, $contact, $authToken);
                        } else {
                            $this->deepLinkAuthHandler->processPhone($chatId, $from, $text, $authToken);
                        }
                        return;

                    case 'awaiting_phone':
                        $this->authHandler->processPhone($chatId, $text);
                        return;

                    case 'awaiting_code':
                        $this->authHandler->processSecretCode($chatId, $text, $currentState);
                        return;
                }
            }
        } else {
            // Agar foydalanuvchi kiritish jarayonida menyu tugmasini bossa, holatni tozalaymiz
            $this->state->clearState($chatId);
        }

        // 4. Buyruqlar va matnli xabarlar marshrutlash
        try {
            switch ($text) {
                case '/start':
                    $this->startHandler->handle($chatId, $from);
                    break;

                case '/help':
                    $this->helpHandler->handleHelp($chatId);
                    break;

                case '💼 Faol vakansiyalar':
                    $this->activeVacanciesHandler->handle($chatId);
                    break;

                case '📁 Vakansiyalarim':
                    $this->myVacanciesHandler->handle($chatId);
                    break;

                case '🔔 Eslatma':
                    $this->staticInfoHandler->handleEslatma($chatId);
                    break;

                case "ℹ️ Ma'lumot":
                    $this->staticInfoHandler->handleInfo($chatId);
                    break;

                case "ℹ️ Saytga tashrif":
                    $this->staticInfoHandler->handleVisitSite($chatId);
                    break;

                case '📞 Kontaktlar':
                    $this->staticInfoHandler->handleContacts($chatId);
                    break;

                case '📞 Kirish':
                case '🔑 Kirish':
                    $this->authHandler->startLogin($chatId);
                    break;

                case '🚪 Chiqish':
                    $this->authHandler->handleLogout($chatId);
                    break;

                case 'Biz haqimizda':
                case 'Mavjud vakansiyalar':
                    $this->helpHandler->handleAbout($chatId);
                    break;

                default:
                    // Noma'lum xabar kelganda menyuni qaytarish
                    $userTelegram = UserTelegram::where('telegram_id', $chatId)->first();
                    $isLoggedIn = $userTelegram && !empty($userTelegram->verified_at);
                    $menu = $isLoggedIn ? $this->keyboard->userMainMenu() : $this->keyboard->guestMainMenu();

                    $this->bot->sendMessage(
                        $chatId,
                        "Iltimos, quyidagi menyudan kerakli bo‘limni tanlang:",
                        $menu
                    );
                    break;
            }
        } catch (\Throwable $e) {
            Log::error("TelegramUpdateRouter execution error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'text' => $text,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
