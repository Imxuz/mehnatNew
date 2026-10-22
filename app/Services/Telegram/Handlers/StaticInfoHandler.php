<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramKeyboardService;
use App\Services\Telegram\TelegramMessageFormatter;
use App\Services\Telegram\TelegramStateService;

class StaticInfoHandler
{
    public function __construct(
        protected TelegramBotService $bot,
        protected TelegramKeyboardService $keyboard,
        protected TelegramMessageFormatter $formatter,
        protected TelegramStateService $state
    ) {}

    /**
     * Eslatma rasmini yuborish
     */
    public function handleEslatma(int|string $chatId): void
    {
        $cachedPhotoId = $this->state->getCachedPhotoId('eslatma');

        if ($cachedPhotoId) {
            $cachedRes = $this->bot->sendPhoto($chatId, $cachedPhotoId);
            if (!empty($cachedRes['ok'])) {
                return;
            }
            // Agar file_id eskirgan bo'lsa, keshni tozalaymiz
            $this->state->setCachedPhotoId('eslatma', '');
        }

        $photoPath = storage_path('app/public/telegram/eslatma.png');

        if (file_exists($photoPath)) {
            $res = $this->bot->sendPhoto($chatId, $photoPath);

            // Agar rasm muvaffaqiyatli ketgan bo'lsa, Telegram file_id sini keshlaymiz
            if (!empty($res['result']['photo'])) {
                $photoArray = $res['result']['photo'];
                $lastPhoto = end($photoArray);
                if (!empty($lastPhoto['file_id'])) {
                    $this->state->setCachedPhotoId('eslatma', $lastPhoto['file_id']);
                }
            }
        } else {
            $this->bot->sendMessage(
                $chatId,
                "⚠️ Eslatma fayli topilmadi."
            );
        }
    }

    /**
     * NKMK axborot portali ma'lumotlari
     */
    public function handleInfo(int|string $chatId): void
    {
        $this->bot->sendMessage(
            $chatId,
            $this->formatter->infoMessage()
        );
    }

    /**
     * Mehnat portaliga tashrif
     */
    public function handleVisitSite(int|string $chatId): void
    {
        $this->bot->sendMessage(
            $chatId,
            $this->formatter->visitSiteMessage()
        );
    }

    /**
     * Aloqa ma'lumotlari
     */
    public function handleContacts(int|string $chatId): void
    {
        $this->bot->sendMessage(
            $chatId,
            $this->formatter->contactsMessage()
        );
    }
}
