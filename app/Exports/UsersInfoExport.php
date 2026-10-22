<?php

namespace App\Exports;

use App\Models\Click;
use App\Services\AiAnalysisService;
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
    protected array $filters;
    protected ?AiAnalysisService $aiAnalysisService;

    public function __construct(int $vacancyId, array $filters = [], ?AiAnalysisService $aiAnalysisService = null)
    {
        $this->vacancyId = $vacancyId;
        $this->filters = $filters;
        $this->aiAnalysisService = $aiAnalysisService ?? app(AiAnalysisService::class);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Column width (A–I)
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(60);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);

                $sheet->getStyle('A1:I1')->applyFromArray([
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
                $sheet->getStyle("A2:I{$highestRow}")->applyFromArray([
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
                $sheet->getStyle('G')->getAlignment()->setWrapText(true);
                $sheet->getStyle('H')->getAlignment()->setWrapText(true);
                $sheet->getStyle('I')->getAlignment()->setWrapText(true);
            },
        ];

    }
    public function columnFormats(): array
    {
        return [
            'B' => '@',
            'C' => '@',
            'E' => '@',
            'H' => '@',
        ];
    }

    public function collection()
    {
        $query = Click::where('vacancy_id', $this->vacancyId);

        $name = trim((string)($this->filters['name'] ?? $this->filters['search'] ?? ''));
        if (!empty($name)) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$name}%"));
        }

        $phone = trim((string)($this->filters['phone'] ?? ''));
        if (!empty($phone)) {
            $query->whereHas('user', fn($q) => $q->where('phone', 'like', "%{$phone}%"));
        }

        $id = trim((string)($this->filters['id'] ?? ''));
        if (!empty($id)) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('user_id', $id);
            });
        }

        if (isset($this->filters['sent']) && $this->filters['sent'] !== '' && $this->filters['sent'] !== 'all') {
            $sent = $this->filters['sent'];
            if ($sent === 'null') {
                $query->whereNull('sent');
            } else {
                $query->where('sent', $sent);
            }
        }

        $sortByScore = $this->filters['sortByScore'] ?? null;
        if ($this->aiAnalysisService && in_array($sortByScore, ['desc', 'above_50'], true)) {
            $sortedOclickIds = $this->aiAnalysisService->getSortedOclickIds($this->vacancyId, $sortByScore);
            if (!empty($sortedOclickIds)) {
                $query->whereIn('id', $sortedOclickIds);
                $idsString = implode(',', $sortedOclickIds);
                $query->orderByRaw("FIELD(id, {$idsString})");
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $clicks = $query->with([
            'user:id,name,pinfl,phone,passport_series,passport_number,birthday,address',
            'doc_histories:id,click_id,dir_demand_id',
            'doc_histories.demand:id,title',
        ])->get();

        if ($this->aiAnalysisService) {
            $this->aiAnalysisService->attachToClicks($clicks, $this->vacancyId);
        }

        return $clicks;
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
            'AI balli',
            'Status',
        ];
    }

    public function map($click): array
    {
        $score = data_get($click->ai_analysis, 'score');
        $aiBall = ($score !== null) ? ((int) $score) . '/10' : '-';

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
            $aiBall,
            optional($click)->sent." " ?? '',
        ];
    }


}
