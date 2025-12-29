<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdderDemandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adderDemands = [
            [
                'id' => 1,
                'dir_demand_id' => 4,
                'adder_text' => '{"ru":"A","uz":"A"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 2,
                'dir_demand_id' => 4,
                'adder_text' => '{"ru":"B","uz":"B"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 5,
                'dir_demand_id' => 4,
                'adder_text' => '{"ru":"C","uz":"C"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 6,
                'dir_demand_id' => 4,
                'adder_text' => '{"ru":"D","uz":"D"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 7,
                'dir_demand_id' => 4,
                'adder_text' => '{"ru":"E","uz":"E"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 8,
                'dir_demand_id' => 1,
                'adder_text' => '{"ru":"Средняя школа","uz":"O‘rta maktab"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 9,
                'dir_demand_id' => 1,
                'adder_text' => '{"ru":"Среднее профессиональное образование (техническая школа, колледж, профессиональное училище)","uz":"O‘rta kasb-hunar ta’limi (texnik maktab, kollej, kasb-hunar maktabi)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 10,
                'dir_demand_id' => 1,
                'adder_text' => '{"ru":"Высшее образование (бакалавриат)","uz":"Oliy (bakalavr)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 11,
                'dir_demand_id' => 1,
                'adder_text' => '{"ru":"Высшее образование (магистратура)","uz":"Oliy (magistr)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 12,
                'dir_demand_id' => 10,
                'adder_text' => '{"ru":"Копия трудовой книжки (через my.gov.uz)","uz":"Mehnat daftarchasidan nusxa (my.gov.uz orqali)"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 13,
                'dir_demand_id' => 2,
                'adder_text' => '{"ru":"Qo\'shimcha sertifikat","uz":"Qo\'shimcha sertifikat"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 14,
                'dir_demand_id' => 9,
                'adder_text' => '{"ru":"A","uz":"A"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 15,
                'dir_demand_id' => 9,
                'adder_text' => '{"ru":"B","uz":"B"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 16,
                'dir_demand_id' => 9,
                'adder_text' => '{"ru":"C","uz":"C"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 17,
                'dir_demand_id' => 9,
                'adder_text' => '{"ru":"D","uz":"D"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 18,
                'dir_demand_id' => 9,
                'adder_text' => '{"ru":"E","uz":"E"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 19,
                'dir_demand_id' => 3,
                'adder_text' => '{"ru":"Нет опыта работы","uz":"Ish tajriba mavjud emas"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 20,
                'dir_demand_id' => 3,
                'adder_text' => '{"ru":"1 год","uz":"1 yil", "number":"1"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 21,
                'dir_demand_id' => 3,
                'adder_text' => '{"ru":"3 год","uz":"3 yil", "number":"3"}',
                'created_at' => null,
                'updated_at' => null
            ],
            [
                'id' => 22,
                'dir_demand_id' => 3,
                'adder_text' => '{"ru":"5 год","uz":"5 yil", "number":"5"}',
                'created_at' => null,
                'updated_at' => null
            ],
        ];

        DB::table('adder_demands')->insert($adderDemands);
    }
}
