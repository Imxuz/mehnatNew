<?php

namespace App\Exports;

use App\Models\DelayWorker;
use Maatwebsite\Excel\Concerns\FromCollection;

class TimeExport implements FromCollection
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows)->map(fn($i) => [$i]);
    }
}
