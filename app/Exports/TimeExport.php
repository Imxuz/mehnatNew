<?php

namespace App\Exports;

use App\Models\DelayWorker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimeExport implements FromCollection, WithHeadings
{
    protected $rows;

    // Konstruktor orqali filtered collection qabul qilinadi
    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    // Qaytaradigan collection
    public function collection()
    {
        // Agar collection ichida associative array bo‘lsa, keylarni Excel qatoriga moslab beradi
        return collect($this->rows)->map(function ($r) {
            return [
                'time'   => $r['time'] ?? null,
                'id'     => $r['id'] ?? null,
                'room'   => $r['room'] ?? null,
                'card'   => $r['card'] ?? null,
                'device' => $r['device'] ?? null,
                'door'   => $r['door'] ?? null,
                'event'  => $r['event'] ?? null,
                'person' => $r['person'] ?? null,
                'status' => $r['status'] ?? null,
            ];
        });
    }

    // Excel faylining sarlavhalari
    public function headings(): array
    {
        return ['Время','ID','№ помещения','№ карты','Устройство','Дверь','Событие','Личное имя','Состояние'];
    }
}
