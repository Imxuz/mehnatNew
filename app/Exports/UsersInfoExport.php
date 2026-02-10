<?php

namespace App\Exports;

use App\Models\Click;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UsersInfoExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithColumnFormatting
{
    protected int $vacancyId;

    public function __construct(int $vacancyId)
    {
        $this->vacancyId = $vacancyId;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Column width (A–D)
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(60);
                $sheet->getColumnDimension('H')->setWidth(15);

                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => Color::COLOR_WHITE],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => '2a7eff',
                        ],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A2:H{$highestRow}")->applyFromArray([
                    'font' => [
                        'italic' => false,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle('B')->getAlignment()->setWrapText(true);
                $sheet->getStyle('D')->getAlignment()->setWrapText(true);
                $sheet->getStyle('H')->getAlignment()->setWrapText(true);
            },
        ];

    }
    public function columnFormats(): array
    {
        return [
            'B' => '@',
            'C' => '@',
        ];
    }

    public function collection()
    {
        return Click::where('vacancy_id', $this->vacancyId)
            ->with([
                'user:id,name,pinfl,phone,passport_series,passport_number,birthday,address',
                'doc_histories:id,click_id,dir_demand_id',
                'doc_histories.demand:id,title',
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'F.I.Sh',
            "Passport ma'lumoti",
            "Tug'ilgan sana",
            "Yashash manzili",
            'PINFL',
            'Telefon',
            'Topshirilgan hujjatlar',
            'Status',
        ];
    }

    public function map($click): array
    {
        return [
            optional($click->user)->name,
            optional($click->user)->passport_series.optional($click->user)->passport_number ?? '',
            optional($click->user)->birthday." " ?? '',
            optional($click->user)->address." " ?? '',
            optional($click->user)->pinfl." " ?? '',
            "+".optional($click->user)->phone." " ?? '',
            $click->doc_histories
                ->map(fn ($doc) => optional($doc->demand)->title
                    ? json_decode(optional($doc->demand)->title, true)['uz']
                    : null)
                ->filter()
                ->join('; '),
            optional($click)->sent." " ?? '',
        ];
    }


}
