<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VerificationLogsExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
{
    public function __construct(protected $query) {}

    public function query()
    {
        return $this->query;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Waktu', 'RS', 'Nomor PIC', 'Keterangan PIC', 'NRP', 'Nama', 'Entitas', 'PT', 'Hasil', 'Feedback', 'IP'];
    }

    /**
     * @param  mixed  $log
     * @return array<int, mixed>
     */
    public function map($log): array
    {
        return [
            $log->created_at->format('Y-m-d H:i:s'),
            $log->hospital_name ?? $log->hospital?->name,
            $log->pic_phone,
            $log->pic_label,
            $log->nrp,
            $log->employee_name,
            $log->group_company,
            $log->company_name,
            $log->result,
            $log->feedback,
            $log->ip,
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:'.$lastColumn.'1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '001F95'],
                    ],
                    'alignment' => [
                        'vertical' => 'center',
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);
            },
        ];
    }
}
