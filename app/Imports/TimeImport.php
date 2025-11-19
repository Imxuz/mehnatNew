<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TimeImport implements ToCollection
{
    public $items = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Headerlarni o‘tkazib yuboramiz

            $this->items[] = [
                'time' => $row[0],  // Время
                'id'   => $row[1],  // ID
                'room' => $row[2],  // № помещения
                'card' => $row[3],  // № карты
                'device' => $row[4],
                'door'   => $row[5],
                'event'  => $row[6],
                'person' => $row[7], // Личное имя
                'status' => $row[8], // Состояние: Вход / Выход
            ];
        }
    }
}
