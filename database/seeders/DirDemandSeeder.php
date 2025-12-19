<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DirDemandSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('dir_demands')->insert([
            [
                'id' => 1,
                'title' => json_encode([
                    'ru' => 'Оценка образования кандидата',
                    'uz' => "Nomzodning ta'limini baholash",
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'education',
                'sort_number' => 8,
                'type' => 'file',
                'formType' => 'select',
                'created_at' => '2025-10-30 03:23:42',
                'updated_at' => '2025-10-30 03:23:42',
            ],
            [
                'id' => 2,
                'title' => json_encode([
                    'ru' => 'Оценка дополнительных сертификатов',
                    'uz' => "Qo'shimcha sertifikatlarni baholash",
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'certificate',
                'sort_number' => 2,
                'type' => 'file',
                'formType' => 'select',
                'created_at' => '2025-10-30 03:23:42',
                'updated_at' => '2025-10-30 03:23:42',
            ],
            [
                'id' => 3,
                'title' => json_encode([
                    'ru' => 'Оценка опыта работы кандидата',
                    'uz' => 'Nomzodning ish tajribasini baholash',
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'workbook',
                'sort_number' => 3,
                'type' => 'file',
                'formType' => 'select',
                'created_at' => '2025-10-30 03:23:42',
                'updated_at' => '2025-10-30 03:23:42',
            ],
            [
                'id' => 4,
                'title' => json_encode([
                    'ru' => 'Оценка водительского удостоверения',
                    'uz' => 'Haydovchilik guvohnomasini baholash',
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'driverLicence',
                'sort_number' => 4,
                'type' => 'file',
                'formType' => 'checkbox',
                'created_at' => '2025-10-30 03:23:42',
                'updated_at' => '2025-10-30 03:23:42',
            ],
//            [
//                'id' => 5,
//                'title' => json_encode([
//                    'ru' => 'Оценка информации о кандидате',
//                    'uz' => "Nomzodning o'zi haqidagi ma'lumotini baholash",
//                ], JSON_UNESCAPED_UNICODE),
//                'name' => '',
//                'sort_number' => 5,
//                'type' => 'text',
//                'created_at' => '2025-10-30 03:23:42',
//                'updated_at' => '2025-10-30 03:23:42',
//            ],
            [
                'id' => 6,
                'title' => json_encode([
                    'ru' => 'Оценка военной службы',
                    'uz' => 'Harbiy xizmatni baholash',
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'militaryCertificate',
                'sort_number' => 6,
                'type' => 'file',
                'formType' => 'select',
                'created_at' => '2025-10-30 03:23:42',
                'updated_at' => '2025-10-30 03:23:42',
            ],
//            [
//                'id' => 7,
//                'title' => json_encode([
//                    'ru' => 'Место проживания кандидата',
//                    'uz' => 'Nomzodning yashash manzili',
//                ], JSON_UNESCAPED_UNICODE),
//                'name' => '',
//                'sort_number' => 7,
//                'type' => 'text',
//                'created_at' => '2025-10-30 03:23:42',
//                'updated_at' => '2025-10-30 03:23:42',
//            ],
            [
                'id' => 8,
                'title' => json_encode([
                    'ru' => 'Паспорт кандидата',
                    'uz' => 'Nomzodning passport',
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'passport',
                'sort_number' => 1,
                'type' => 'file',
                'formType' => 'select',
                'created_at' => '2025-11-10 12:18:11',
                'updated_at' => '2025-11-10 12:18:11',
            ],
            [
                'id' => 9,
                'title' => json_encode([
                    'ru' => 'Удостоверение водителя трактора',
                    'uz' => 'Traktor mashinist guvohnomasi',
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'tractor',
                'sort_number' => 9,
                'type' => 'file',
                'formType' => 'checkbox',
                'created_at' => '2025-11-10 12:21:34',
                'updated_at' => '2025-11-10 12:21:34',
            ],
            [
                'id' => 10,
                'title' => json_encode([
                    'ru' => 'Трудовая книжка',
                    'uz' => 'Mehnat daftarchasi',
                ], JSON_UNESCAPED_UNICODE),
                'name' => 'tractor',
                'sort_number' => 10,
                'type' => 'file',
                'formType' => 'select',
                'created_at' => '2025-11-10 12:21:34',
                'updated_at' => '2025-11-10 12:21:34',
            ],
        ]);
    }
}
