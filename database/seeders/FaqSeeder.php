<?php

namespace Database\Seeders;

use App\Models\Faqs;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $faqs = [
            [
                'id' => 1,
                'question' => '{"ru": "ЧТО НУЖНО СДЕЛАТЬ, ДЛЯ ТРУДОУСТРОЙСТВА В АО «НГМК»?", "uz": "«NKMK» AJGA ISHGA KIRISH UCHUN NIMA QILISH KERAK?"}',
                'answer' => '{"ru": "1. Пройти регистрацию на этом сайте<br>2. Заполнить анкету, выбрать подходящую вакансию и оставить свой отклик.<br>3. Ожидайте! Если Ваша кандидатура подходит на выбранную вакансию, с вами свяжутся соответствующие специалисты.", "uz": "1. Ushbu saytda registratsiyadan o\'ting.<br>2. Bo\'sh ish o\'rinini tanlang, anketani to\'ldiring va arizangizni qoldiring.<br>3. Kuting. Sizning nomzodingiz tanlagan bo\'sh ish o\'rniga mos bo\'lsa, tegishli mutaxassislar siz bilan bog\'lanadi."}',
            ],
            [
                'id' => 2,
                'question' => '{"ru": "Мне никто не позвонил по поводу работы", "uz": "Hech kim menga ish haqida qo\'ng\'iroq qilmadi"}',
                'answer' => '{"ru": "Если с Вами не связались в течении 30 рабочих дней, после Вашего отклика, это означает, что Ваша кандидатура на текущий момент не подходит по критериям для выбранной вакансии.", "uz": "Agar sizga 30 ish kuni davomida qo\'ng\'iroq qilmasalar, bu Sizning nozodingiz Siz tanlagan bo\'sh ish o\'rniga o\'rnatilgan talablarga mos kelmaganligini anglatadi."}',
            ],
            [
                'id' => 3,
                'question' => '{"ru": "КАКАЯ ЗАРАБОТНАЯ ПЛАТА В АО «НКМК»", "uz": "«NKMK» AJDA OYLIK MAOSHI QANCHA?"}',
                'answer' => '{"ru": "Заработная плата будет указана в условиях вакансии.<br>Если не указано, то уровень заработной платы обсуждается индивидуально на собеседовании.", "uz": "Ish haqi bo\'sh ish o\'rini shartlarida ko\'rsatilgan.<br>Agar u yerda ko\'rsatilmagan bo\'lsa, unda ish haqi darajasi suhbatda alohida muhokama qilinadi."}',
            ],
            [
                'id' => 4,
                'question' => '{"ru": "Когда будет собеседование?", "uz": "Tanlov qachon bo\'ladi?"}',
                'answer' => '{"ru": "После регистрации на сайте и отклика на вакансию, если Ваш опыт, образование, и иные требования соответствуют квалификационным требованиям вакансии, Вам будет назначено собеседование специалистом по телефону или с помощью sms-оповещения.", "uz": "Saytda ro\'yxatdan o\'tib, bo\'sh ish o\'riniga ariza topshirganingizdan so\'ng tajribangiz, ma\'lumotingiz va boshqa talablaringiz bo\'sh ish o\'rniga mos kelsa, mutaxassis tomonidan telefon orqali yoki SMS-xabarnoma orqali suhbat tayinlanadi."}',
            ],
            [
                'id' => 5,
                'question' => '{"ru": "КАКИЕ ДОКУМЕНТЫ ИМЕТЬ ПРИ СЕБЕ НА СОБЕСЕДОВАНИЕ?", "uz": "Suhbatga kelish uchun qanday hujjatlar kerak?"}',
                'answer' => '{"ru": "Документ, удостоверяющий личность (паспорт, ID карта), документы, подтверждающие специальность или профессию.", "uz": "Shaxsini tasdiqlovchi xujjat (pasport, ID karta), mutaxassisligi yoki kasbini tasdiqlovchi xujjatlar."}',
            ],
            [
                'id' => 7,
                'question' => '{"ru": "ЕСТЬ ЛИ УСЛОВИЯ ДЛЯ ПИТАНИЯ?", "uz": "OVQATLANISH UCHUN SHAROIT MAVJUDMI?"}',
                'answer' => '{"ru": "На территории АО «НГМК» есть столовые. Питание оплачивается самостоятельно работником. <br> Для работников с вредными условиями труда на производстве предусмотрено лечебно-профилактическое питание.", "uz": "«NKMK» AJ korxonalari hududida oshxonalar mavjud. «Zararli» ishlab chiqarishdagi ishchi-xodimlar uchun oziq-ovqat talonlari ko\'zda tutilgan."}',
            ],
            [
                'id' => 8,
                'question' => '{"ru": "Предоставляется ли жилье иногородним?", "uz": "CHETDAN KELGANLAR UCHUN UY-JOY TAQDIM ETILADIMI?"}',
                'answer' => '{"ru": "Данный вопрос обсуждается индивидуально с каждым кандидатом.", "uz": "Bu masala har bir nomzod bilan alohida muhokama qilinadi."}',
            ],
            [
                'id' => 10,
                'question' => '{"ru": "Возможен ли карьерный рост?", "uz": "LAVOZIM PAG\'ONASIDA O\'SISHI MAVJUDMI?"}',
                'answer' => '{"ru": "Да, на Комбинате возможен и приветствуется карьерный рост как вертикально (до руководителя), так и горизонтально (повышение разряда).", "uz": "Ha, kombinatda vertikal (boshliq darajasigacha) va gorizontal (razryad ko\'tarilishi) bo\'yicha karyera o\'sishi mumkin va rag\'batlantiriladi."}',
            ],
            [
                'id' => 11,
                'question' => '{"ru": "Как узнать дату тестирования?", "uz": "Sinov sanasini qanday bilsam bo\'ladi?"}',
                'answer' => '{"ru": "О дате тестирования Вам сообщится по sms или звонком специалиста.", "uz": "Test sanasi haqida SMS yoki mutaxassis qo\'ng\'irog\'i orqali xabardor qilinadi."}',
            ],
            [
                'id' => 12,
                'question' => '{"ru": "Я НЕ ПРОШЕЛ В КОНКУРСЕ, МНЕ ЗАНОВО ПРОЙТИ РЕГИСТРАЦИЮ?", "uz": "Men tanlovdan o\'ta olmadim. Qayta ro\'yxatdan o\'tishim kerakmi?"}',
                'answer' => '{"ru": "Нет, Ваши данные уже находятся в базе Комбината, также вы можете оставлять отклики на другие подходящие Вам вакансии.", "uz": "Yo\'q, Ro\'yxatdan faqatgina bir marotaba o\'tiladi. Siz o\'zizga mos kelgan boshqa vakant lavozimlarga ariza topshirishingiz mumkin."}',
            ],
            [
                'id' => 14,
                'question' => '{"ru": "Кому звонить для консультации по трудоустройству?", "uz": "ISHGA KIRISH BO\'YICHA MASLAHAT UCHUN KIMGA MUROJAAT QILISH KERAK?"}',
                'answer' => '{"ru": "На этом сайте размещены все вакансии АО «НГМК». Если на нашем сайте нет подходящей Вам вакансии, это означает, что других вакансий в АО «НКМК» на данный момент нет.", "uz": "Bo\'sh ish o\'rinlarimiz ushbu saytga joylashtiriladi. Agarda Sizga mos keladigan bo\'sh ish o\'rni saytimizda bo\'lmasa, demak ayni damda «NKMK» AJda boshqa bo\'sh ish o\'rinlari mavjud emas."}',
            ],
            [
                'id' => 27,
                'question' => '{"ru": "У меня нет компьютера/смартфона. Как пройти регистрацию?", "uz": "Menda kompyuter/smartfon yo\'q. Qanday qilib ro\'yxatdan o\'tishim mumkin?"}',
                'answer' => '{"ru": "Вы можете прийти на вход предприятия комбината или учебного центра, ближайшего к вам, и пройти регистрацию.", "uz": "Sizga eng yaqin bo\'lgan kombinat korxonasi yoki o\'quv markazining kirish joyiga kelib, ro\'yxatdan o\'tishingiz mumkin."}',
            ],
        ];

        foreach ($faqs as $faq) {
            Faqs::create($faq);
        }
    }
}
