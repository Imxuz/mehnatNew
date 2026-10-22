<?php

namespace App\Services\Telegram;

class TelegramMessageFormatter
{
    /**
     * Ko'p tilli maydonlardan matnni ajratib olish (JSON yoki Array)
     */
    public function parseLang(mixed $data, string $lang = 'uz'): string
    {
        if (empty($data)) {
            return '';
        }

        if (is_array($data)) {
            return (string) ($data[$lang] ?? $data['ru'] ?? reset($data) ?? '');
        }

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return (string) ($decoded[$lang] ?? $decoded['ru'] ?? reset($decoded) ?? '');
            }
            return $data;
        }

        return (string) $data;
    }

    /**
     * /start xush kelibsiz xabari
     */
    public function welcomeMessage(bool $isLoggedIn, ?string $name = null): string
    {
        if ($isLoggedIn) {
            $nameStr = $name ? " " . htmlspecialchars($name, ENT_QUOTES) : "";
            return "✨ <b>Assalomu alaykum, hurmatli{$nameStr}!</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "🏢 <b>NKMK AJ Bo‘sh ish o‘rinlari portali</b>\n"
                . "💼 <i>Portalning rasmiy axborot botiga xush kelibsiz.</i>\n\n"
                . "📱 <i>Tizimdan to‘liq foydalanishingiz mumkin.</i>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
        }

        return "👋 <b>Assalomu alaykum!</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 <b>NKMK AJ Bo‘sh ish o‘rinlari portali</b>\n"
            . "💼 <i>Portalning rasmiy axborot botiga xush kelibsiz.</i>\n\n"
            . "🔑 <i>Tizim imkoniyatlaridan foydalanish uchun iltimos shaxsiy kabinetingiz orqali login qiling.</i>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";
    }

    /**
     * Region / Hudud matnini xavfsiz formatlash
     */
    public function formatRegion(mixed $v): string
    {
        $region = $this->parseLang(is_object($v) ? ($v->region->title ?? '') : ($v['region']['title'] ?? ''));
        $parentObj = is_object($v)
            ? ($v->region->parentRegion ?? ($v->region->parent_region ?? null))
            : ($v['region']['parentRegion'] ?? ($v['region']['parent_region'] ?? null));
        $parentRegion = $this->parseLang(is_object($parentObj) ? ($parentObj->title ?? '') : ($parentObj['title'] ?? ''));

        if ($parentRegion && $region) {
            return "{$parentRegion} / {$region}";
        }

        return $region ?: ($parentRegion ?: 'NKMK');
    }

    /**
     * Talab qiymatini (AdderDemand) to'plam yoki obyekt/matndan xavfsiz olish
     */
    public function formatDemandValue(mixed $adderTexts): string
    {
        if (empty($adderTexts)) {
            return '';
        }

        if ($adderTexts instanceof \Illuminate\Support\Collection || is_array($adderTexts)) {
            $vals = [];
            foreach ($adderTexts as $item) {
                $val = $this->parseLang(is_object($item) ? ($item->adder_text ?? '') : ($item['adder_text'] ?? ''));
                if ($val !== '') {
                    $vals[] = $val;
                }
            }
            return implode(", ", $vals);
        }

        if (is_object($adderTexts)) {
            return $this->parseLang($adderTexts->adder_text ?? '');
        }

        return (string) $adderTexts;
    }

    /**
     * Vakansiya talablarini formatlash
     */
    public function formatDemandsText(mixed $demands): string
    {
        $demandsText = "";
        if (empty($demands)) {
            return $demandsText;
        }

        foreach ($demands as $d) {
            $demandName = $this->parseLang(is_object($d) ? ($d->dir_demand->title ?? '') : ($d['dir_demand']['title'] ?? ''));
            $adderTexts = is_object($d) ? ($d->adder_texts ?? null) : ($d['adder_texts'] ?? null);
            $demandVal = $this->formatDemandValue($adderTexts);

            if ($demandName) {
                $demandsText .= "  🔹 <i>{$demandName}:</i> <b>" . ($demandVal ?: '-') . "</b>\n";
            }
        }

        return $demandsText;
    }

    /**
     * Vakansiyaning o'ziga xos talablarini Telegram matniga aylantirish
     */
    public function formatSpecialDemandsText(mixed $specialDemands): string
    {
        $text = "";
        if (empty($specialDemands)) {
            return $text;
        }

        foreach ($specialDemands as $sd) {
            $demandName = "";
            $dirDemand = is_object($sd) ? ($sd->dir_demand ?? null) : ($sd['dir_demand'] ?? null);
            if ($dirDemand) {
                $rawTitle = is_object($dirDemand) ? ($dirDemand->title ?? '') : ($dirDemand['title'] ?? '');
                $demandName = $this->parseLang($rawTitle);
            }
            $customText = is_object($sd) ? ($sd->text ?? '') : ($sd['text'] ?? '');
            if ($demandName) {
                $text .= "  ⭐ <i>{$demandName}:</i> <b>{$customText}</b>\n";
            } else {
                $text .= "  ⭐ <b>{$customText}</b>\n";
            }
        }

        return $text;
    }

    /**
     * Faol vakansiyalar ro'yxatini formatlash
     */
    public function formatActiveVacancies($vacancies): string
    {
        if (count($vacancies) === 0) {
            return "⚠️ <b>Hozirda faol bo‘sh ish o‘rinlari mavjud emas.</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Yangi vakansiyalar e’lon qilinganda bot orqali xabarnoma yuboriladi.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
        }

        $text = "💼 <b>NKMK AJ Bo'sh ish o'rinlari</b>\n"
            . "⚡️ <b>Faol vakansiyalar ro'yxati</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($vacancies as $v) {
            $title = $this->parseLang(is_object($v) ? ($v->occupation->occupation ?? '') : ($v['occupation']['occupation'] ?? ''));
            $desc = $this->parseLang(is_object($v) ? ($v->description ?? '') : ($v['description'] ?? ''));
            $hudud = $this->formatRegion($v);
            $demandsText = $this->formatDemandsText($v->demands ?? ($v['demands'] ?? []));

            $openAt = is_object($v) ? ($v->open_at ?? '') : ($v['open_at'] ?? '');
            $closeAt = is_object($v) ? ($v->close_at ?? '') : ($v['close_at'] ?? '');

            $text .= "📌 <b>{$title}</b>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "📍 <b>Hudud:</b> <i>{$hudud}</i>\n"
                . "📅 <b>Ochilgan:</b> <i>{$openAt}</i>\n"
                . "⏳ <b>Yopiladi:</b> <i>{$closeAt}</i>\n\n";

            if ($demandsText) {
                $text .= "📋 <b>Talablar:</b>\n{$demandsText}\n";
            }

            $specialDemandsText = $this->formatSpecialDemandsText($v->specialDemands ?? ($v['special_demands'] ?? ($v['specialDemands'] ?? [])));
            if ($specialDemandsText) {
                $text .= "💡 <b>Xos talablar:</b>\n{$specialDemandsText}\n";
            }

            if ($desc) {
                $text .= "📝 <b>Qisqacha tavsif:</b>\n<i>{$desc}</i>\n\n";
            }
        }

        return $text;
    }

    /**
     * Foydalanuvchining o'z arizalarini formatlash
     */
    public function formatMyVacancies($vacancies): string
    {
        if (count($vacancies) === 0) {
            return "📁 <b>Mening arizalarim</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "Siz hali birorta ham bo‘sh ish o‘rniga ariza topshirmagansiz.\n\n"
                . "Faol vakansiyalarni ko‘rish va ariza topshirish uchun <b>💼 Faol vakansiyalar</b> bo‘limiga o‘ting.\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
        }

        $text = "📁 <b>NKMK AJ Bo'sh ish o'rinlari</b>\n"
            . "📝 <b>Mening arizalarim ro'yxati</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($vacancies as $v) {
            $title = $this->parseLang(is_object($v) ? ($v->occupation->occupation ?? '') : ($v['occupation']['occupation'] ?? ''));
            $desc = $this->parseLang(is_object($v) ? ($v->description ?? '') : ($v['description'] ?? ''));
            $hudud = $this->formatRegion($v);
            $demandsText = $this->formatDemandsText($v->demands ?? ($v['demands'] ?? []));

            $sent = is_object($v) ? ($v->telegClick->sent ?? null) : ($v['teleg_click']['sent'] ?? null);

            if ($sent === 'cancel') {
                $status = "❌ <b>Rad etildi</b>\n<i>Nomzodingiz ushbu vakansiya talablariga mos kelmadi.</i>";
            } elseif ($sent === 'accept') {
                $status = "✅ <b>Qabul qilindi</b>\n<i>Tabriklaymiz! Siz tanlovning keyingi bosqichiga o‘tdingiz.\n📞 Tez orada mas'ul xodimlarimiz siz bilan bog‘lanishadi.</i>";
            } else {
                $status = "⏳ <b>Ko‘rib chiqilmoqda</b>\n<i>Hujjatlaringiz ayni damda mas'ul xodimlar tomonidan tekshirilmoqda.</i>";
            }

            $openAt = is_object($v) ? ($v->open_at ?? '') : ($v['open_at'] ?? '');
            $closeAt = is_object($v) ? ($v->close_at ?? '') : ($v['close_at'] ?? '');

            $text .= "📌 <b>{$title}</b>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "📍 <b>Hudud:</b> <i>{$hudud}</i>\n"
                . "📅 <b>Ochilgan:</b> <i>{$openAt}</i>\n"
                . "⏳ <b>Yopiladi:</b> <i>{$closeAt}</i>\n\n";

            if ($demandsText) {
                $text .= "📋 <b>Topshirilgan ariza talablari:</b>\n{$demandsText}\n";
            }

            if ($desc) {
                $text .= "📝 <b>Qisqacha tavsif:</b>\n<i>{$desc}</i>\n\n";
            }

            $text .= "🔔 <b>Ariza holati:</b>\n{$status}\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }

        return $text;
    }

    /**
     * Yangi e'lon qilingan vakansiyani ommaviy jo'natish (Broadcast) xabari
     */
    public function formatNewVacancyBroadcast($vacancy): string
    {
        $title = $this->parseLang(is_object($vacancy) ? ($vacancy->occupation->occupation ?? '') : ($vacancy['occupation']['occupation'] ?? ''));
        $desc = $this->parseLang(is_object($vacancy) ? ($vacancy->description ?? '') : ($vacancy['description'] ?? ''));
        $hudud = $this->formatRegion($vacancy);
        $demandsText = $this->formatDemandsText($vacancy->demands ?? ($vacancy['demands'] ?? []));
        $specialDemandsText = $this->formatSpecialDemandsText($vacancy->specialDemands ?? ($vacancy['special_demands'] ?? ($vacancy['specialDemands'] ?? [])));

        $openAt = is_object($vacancy) ? ($vacancy->open_at ?? '') : ($vacancy['open_at'] ?? '');
        $closeAt = is_object($vacancy) ? ($vacancy->close_at ?? '') : ($vacancy['close_at'] ?? '');

        $text = "📢 <b>Yangi vakansiya e'lon qilindi!</b>\n\n"
            . "📌 <b>{$title}</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "📍 <b>Hudud:</b> <i>{$hudud}</i>\n"
            . "📅 <b>Ochilgan:</b> <i>{$openAt}</i>\n"
            . "⏳ <b>Yopiladi:</b> <i>{$closeAt}</i>\n\n"
            . "📋 <b>Nomzodga qo'yiladigan talablar:</b>\n{$demandsText}\n";

        if ($specialDemandsText) {
            $text .= "💡 <b>Xos talablar:</b>\n{$specialDemandsText}\n";
        }

        $text .= "📝 <b>Qisqacha tavsif:</b>\n<i>" . ($desc ?: 'Mavjud emas') . "</i>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";

        return $text;
    }

    /**
     * Kontaktlar ma'lumoti
     */
    public function contactsMessage(): string
    {
        $address = config('telegram.contacts.address', "Navoiy shahri, Navoiy ko'chasi, 27-uy.");
        $email = config('telegram.contacts.email', 'info@ngmk.uz');
        $hotline = config('telegram.contacts.hotline', '(79) 227-77-77');

        return "📞 <b>NKMK AJ Aloqa ma'lumotlari</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "📍 <b>Manzil:</b> <i>{$address}</i>\n"
            . "✉️ <b>E-mail:</b> <i>{$email}</i>\n"
            . "☎️ <b>Ishonch telefoni:</b> <code>{$hotline}</code>\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";
    }

    /**
     * NKMK ma'lumoti
     */
    public function infoMessage(): string
    {
        return "ℹ️ <b>NKMK AJ Rasmiy axborot xizmati</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 «Navoiy kon-metallurgiya kombinati» AJ bo‘sh ish o‘rinlari portali rasmiy Telegram axborot boti.\n\n"
            . "Savollar yoki takliflar bo‘lsa, <b>📞 Kontaktlar</b> bo‘limi orqali mas'ul xodimlar bilan bog‘lanishingiz mumkin.\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";
    }

    /**
     * Saytga tashrif matni
     */
    public function visitSiteMessage(): string
    {
        return "💻 <b>NKMK AJ Boʻsh ish oʻrinlari portali</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "💼 Tizim orqali arizalaringiz holatini kuzatib borishingiz va yangi bo'sh ish o'rinlari haqida tezkor xabardor bo'lishingiz mumkin.\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";
    }

    /**
     * Administratorlar yordam ro'yxati (/help)
     */
    public function helpMessage(array $admins): string
    {
        if (empty($admins)) {
            return "ℹ️ <b>Yordam bo'limi</b>\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                . "🏢 <b>NKMK AJ Bo'sh ish o'rinlari portali</b>\n"
                . "━━━━━━━━━━━━━━━━━━━━━━━━";
        }

        $names = implode("\n", array_map(fn ($name) => "  • <i>{$name}</i>", $admins));

        return "ℹ️ <b>Yordam bo'limi</b>\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 <b>NKMK AJ Bo'sh ish o'rinlari portali</b>\n\n"
            . "👨‍💼 <b>Tizim administratorlari:</b>\n{$names}\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━";
    }
}
