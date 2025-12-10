<?php

namespace App\Imports;

use App\Models\Occupation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class OccupationsImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }
            $ru = $row[1] ?? null;
            $uz = $row[3] ?? null;

            if (empty($ru) || empty($uz)) {
                continue;
            }

            Occupation::create([
                'occupation' => json_encode([
                    'ru' => trim($ru),
                    'uz' => trim($uz),
                ], JSON_UNESCAPED_UNICODE),
                'demand' => json_encode([
                    'ru' => trim($ru),
                    'uz' => trim($uz),
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
}
