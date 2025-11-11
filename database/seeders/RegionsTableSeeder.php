<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $regions = [
//            [
//                'id' => 1,
//                'sub_region_id' => null,
//                'name' => 'all',
//                'title' => '{"ru": "Все", "uz": "Barcha"}',
//                'created_at' => null,
//                'updated_at' => null
//            ],
            [
                'id' => 2,
                'sub_region_id' => null,
                'name' => 'navoiCity',
                'title' => '{"ru": "Город Навои", "uz": "Navoiy shahri"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 3,
                'sub_region_id' => null,
                'name' => 'navoi',
                'title' => '{"ru": "Навоийская область Нуратинский район (Кызилча)", "uz": "Navoiy viloyati Nurota tumani (Qizilcha)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 4,
                'sub_region_id' => null,
                'name' => 'zarafshan',
                'title' => '{"ru": "Город Зарафшан", "uz": "Zarafshon shahri"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 5,
                'sub_region_id' => null,
                'name' => 'uchkuduk',
                'title' => '{"ru": "Навоийская область Учкудукский район", "uz": "Navoiy viloyati Uchquduq tumani"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 6,
                'sub_region_id' => null,
                'name' => 'kushrabat',
                'title' => '{"ru": "Самаркадская область Кушрабатский район (Заркент)", "uz": "Samarqand viloyati Qushrabod tumani (Zarkent)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 7,
                'sub_region_id' => null,
                'name' => 'paxtachi',
                'title' => '{"ru": "Самаркадская область Пахтачинский район", "uz": "Samarqand viloyati Paxtachi tumani"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 8,
                'sub_region_id' => null,
                'name' => 'gallyaaral',
                'title' => '{"ru": "Джизакская область Галляаральский район (Марджанбулак)", "uz": "Jizzax viloyati G`allaorol tumani (Marjonbuloq)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 9,
                'sub_region_id' => null,
                'name' => 'tashkentCity',
                'title' => '{"ru": "Город Ташкент", "uz": "Toshkent shahri"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 10,
                'sub_region_id' => null,
                'name' => 'moskow',
                'title' => '{"ru": "Город Москва", "uz": "Moskva shahri"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 11,
                'sub_region_id' => null,
                'name' => 'taxiatash',
                'title' => '{"ru": "Республика Каракалпакстан город Тахиаташ", "uz": "Qoraqolpog`iston Respublikasi Taxiatosh shahri"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 12,
                'sub_region_id' => null,
                'name' => 'surkhandaryo',
                'title' => '{"ru": "Сурхандарьинская область", "uz": "Surxandaryo viloyati"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 13,
                'sub_region_id' => 2,
                'name' => 'centralManagement',
                'title' => '{"ru": "Центральный аппарат (Управление)", "uz": "Markaziy apparat (Boshqarma)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 14,
                'sub_region_id' => 2,
                'name' => 'NMZ',
                'title' => '{"ru": "ПО «Навоийский машиностроительный завод»", "uz": "Navoiy mashinasozlik zavodi ishlab chiqarish birlashmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 15,
                'sub_region_id' => 2,
                'name' => 'CMTB',
                'title' => '{"ru": "Центральная материально техническая база", "uz": "Марказий моддий-техник база"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 16,
                'sub_region_id' => 2,
                'name' => 'centralLabaratory',
                'title' => '{"ru": "Центральная научно исследовательская лаборатория", "uz": "Markaziy ilmiy-tadqiqot laboratoriyasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 17,
                'sub_region_id' => 2,
                'name' => 'kyzylkumMining',
                'title' => '{"ru": "Рудоуправления «Кызылкум»", "uz": "Qizilqum kon boshqarmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 18,
                'sub_region_id' => 2,
                'name' => 'motorDepot3',
                'title' => '{"ru": "Автобаза №3", "uz": "3-sonli Avtobaza"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 19,
                'sub_region_id' => 2,
                'name' => 'substationsWorkshop',
                'title' => '{"ru": "Цех сетей и подстанций", "uz": "Tarmoqlar va podstansiyalar sexi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 20,
                'sub_region_id' => 2,
                'name' => 'geologicalExpedition',
                'title' => '{"ru": "Геологоразведочная экспедиция", "uz": "Geologiya-qidiruv ekspedisiyasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 21,
                'sub_region_id' => 2,
                'name' => 'constructionNavoi',
                'title' => '{"ru": "Строительно-монтажное управление «Навои»", "uz": "Navoiy qurilish-montaj boshqarmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 22,
                'sub_region_id' => 2,
                'name' => 'automationDepartment',
                'title' => '{"ru": "Управление автоматизации производства", "uz": "Ishlab chiqarishni avtomatlashtirish boshqarmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 23,
                'sub_region_id' => 2,
                'name' => 'trustPromelektromontazh',
                'title' => '{"ru": "Монтажно-строительный трест «Промэлектромонтаж»", "uz": "Sanoatelektromontaj montaj-qurilish tresti"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 24,
                'sub_region_id' => 3,
                'name' => '',
                'title' => '{"ru": "РУ «Кызылкум» - Рудник «Пистали»", "uz": "Qizilqum kon boshqarmasi - Pistali koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 25,
                'sub_region_id' => 3,
                'name' => '',
                'title' => '{"ru": "РУ «Кызылкум» - ГМЗ-6", "uz": "Qizilqum kon boshqarmasi - 6-sonli GMZ"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 26,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Управление Центрального Рудоуправления", "uz": "Markaziy kon boshqarmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 27,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Центр информационно-коммуникационных технологий", "uz": "Axborot-kommunikatsiya texnologiyalari markazi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 28,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Гидрометаллургический завод №2", "uz": "2-sonli Gidrometallurgiya zavodi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 29,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Гидрометаллургический завод №5", "uz": "5-sonli Gidrometallurgiya zavodi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 30,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Гидрометаллургический завод №7", "uz": "7-sonli Gidrometallurgiya zavodi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 31,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Рудник «ЦКВЗ»", "uz": "OUEOS koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 32,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Рудник «Мурунтау»", "uz": "Muruntog koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 33,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Рудник «Ауминзо-Амантой»", "uz": "Auminzo-Amantoy koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 34,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Шахта «Мурунтау»", "uz": "Muruntog shaxtasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 35,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Зарафшанское управление строительства", "uz": "Zarafshon qurilish boshqarmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 36,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Автобаза №7", "uz": "7-sonli Avtobaza"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 37,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "Автобаза №9", "uz": "9-sonli Avtobaza"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 38,
                'sub_region_id' => 4,
                'name' => '',
                'title' => '{"ru": "РУ «Кызылкум» - Каръера «Аристантау»", "uz": "Qizilqum kon boshqarmasi - Aristantau karyeri"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 39,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Управление Северного Рудоуправления", "uz": "Shimoliy kon boshqarmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 40,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Центр информационно-коммуникационных технологий", "uz": "Axborot-kommunikatsiya texnologiyalari markazi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 41,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Автобаза №1", "uz": "1-sonli Avtobaza"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 42,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Рудник открытой добычи «Даугизтау»", "uz": "Daugiztau ochiq usulda qazib olish koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 43,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Рудник открытой добычи «Восточный»", "uz": "Sharqiy ochiq usulda qazib olish koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 44,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Гидрометаллургический завод №3", "uz": "3-sonli Gidrometallurgiya zavodi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 45,
                'sub_region_id' => 6,
                'name' => '',
                'title' => '{"ru": "Управление Южного Рудоуправления", "uz": "Janubiy kon boshqarmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 46,
                'sub_region_id' => 6,
                'name' => '',
                'title' => '{"ru": "Центр информационно-коммуникационных технологий", "uz": "Ахборот-коммуникация технологиялари маркази"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 47,
                'sub_region_id' => 6,
                'name' => '',
                'title' => '{"ru": "Автобаза №10", "uz": "10-sonli Avtobaza"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 48,
                'sub_region_id' => 6,
                'name' => '',
                'title' => '{"ru": "Гидрометаллургический завод №4", "uz": "4-sonli Gidrometallurgiya zavodi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 49,
                'sub_region_id' => 6,
                'name' => '',
                'title' => '{"ru": "Рудник «Зармитан»", "uz": "Zarmitan koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 50,
                'sub_region_id' => 6,
                'name' => '',
                'title' => '{"ru": "Рудник «Гужумсай»", "uz": "Gujumsay koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 51,
                'sub_region_id' => 7,
                'name' => '',
                'title' => '{"ru": "РУ «Кызылкум» - Рудник Каракутан", "uz": "Qizilqum kon boshqarmasi - Qoraquton koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 52,
                'sub_region_id' => 8,
                'name' => '',
                'title' => '{"ru": "ЮРУ - Рудник «Маржанбулак»", "uz": "Janubiy kon boshqarmasi - Marjanbuloq koni"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 53,
                'sub_region_id' => 9,
                'name' => '',
                'title' => '{"ru": "Представительство в г. Ташкент", "uz": "Toshkent shahridagi vakolatxona"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 54,
                'sub_region_id' => 9,
                'name' => '',
                'title' => '{"ru": "Ташкентская материально-техническая база", "uz": "Toshkent moddiy-texnik bazasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 55,
                'sub_region_id' => 10,
                'name' => '',
                'title' => '{"ru": "Представительство в г. Москва", "uz": "Moskva shahridagi vakolatxona"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 56,
                'sub_region_id' => 11,
                'name' => '',
                'title' => '{"ru": "ПО «НМЗ» - Тахиаташский завод", "uz": "NMZ IChB - Taxiatosh zavodi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 57,
                'sub_region_id' => 12,
                'name' => '',
                'title' => '{"ru": "ПО «НМЗ» - Термезеский механический завод", "uz": "NMZ IChB - Termiz mexanika zavodi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 76,
                'sub_region_id' => 13,
                'name' => 'trainingCenter',
                'title' => '{"ru": "Учебный центр", "uz": "Oquv markazi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 77,
                'sub_region_id' => 13,
                'name' => 'DICT',
                'title' => '{"ru": "Управление информационно-коммуникационных технологий", "uz": "Ахборот коммуникация-технологиялари бошқармаси"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 78,
                'sub_region_id' => 17,
                'name' => 'GMZ1',
                'title' => '{"ru": "ГМЗ-1", "uz": "1-sonli GMZ"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 79,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Участок по ремонту электрооборудования", "uz": "Elektr jihozlarini tamirlash uchastkasi"}',
                'created_at' => '2024-08-23 05:42:41',
                'updated_at' => '2024-08-23 05:42:41'
            ],
            [
                'id' => 80,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Цех ремонта горного оборудования", "uz": "Kon jihozlarini tamirlash sexi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 81,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Центральная материально-техническая база", "uz": "Markaziy moddiy-texnik bazasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 82,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Цех тепловодогазоснабжения и канализации", "uz": "Issiqlik-suv-gaz taminoti va oqova suv sexi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 83,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Железнодорожный цех", "uz": "Temir yol sexi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 84,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Центральная лаборатория контрольно-измерительных приборов и автоматики", "uz": "Nazorat olchov asboblari va avtomatika markaziy laboratoriyasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 85,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Управление автомобильного транспорта", "uz": "Автомобиль транспорт бошқармаси"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 86,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Центральная лаборатория контроля условий труда, охраны окружающей среды и рационального использования природных ресурсов", "uz": "Mehnat sharoitlari nazorati, atrof-muhitni muhofazasi va tabiiy resurslardan oqilona foydalanish markaziy laboratoriyasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 87,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Центральная физико-химическая лаборатория", "uz": "Markaziy fizik-kimyoviy laboratoriya"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 88,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Отряд ведомственной военизированной охраны", "uz": "Idoraviy harbiylashtirilgan qoriqlash otryadi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 89,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Отделение технического контроля", "uz": "Texnik nazorat bolinmasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 90,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Ремонтно-строительный участок", "uz": "Tamirlash-qurilish uchastkasi"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 91,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Ремонтно-механический цех", "uz": "Таъмирлаш-механик цехи"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 92,
                'sub_region_id' => 5,
                'name' => '',
                'title' => '{"ru": "Цех сетей и подстанций", "uz": "Tarmoqlar va podstansiyalar sexi"}',
                'created_at' => null,
                'updated_at' => null
            ]
        ];

        DB::table('regions')->insert($regions);
    }
}
