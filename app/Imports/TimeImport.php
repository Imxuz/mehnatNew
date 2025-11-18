<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TimeImport implements ToCollection
{
    public $items;

    public function collection(Collection $rows)
    {
        $this->items = $rows->pluck(0);
    }
}
