<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpiringDocumentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'ชื่อแรงงาน',
            'บริษัทนายจ้าง',
            'ประเภทเอกสาร',
            'วันหมดอายุ',
            'เหลืออีก (วัน)',
            'สถานะเอกสาร',
            'ไฟล์เอกสาร',
        ];
    }

    public function map($row): array
    {
        $today = now()->startOfDay();
        $expiryDate = $row['expiry_date']->copy()->startOfDay();

        return [
            $row['worker']->full_name_th ?: ($row['worker']->full_name_en ?: '-'),
            $row['worker']->employer?->company_name ?: '-',
            $row['label'],
            $row['expiry_date']->format('d/m/Y'),
            $today->diffInDays($expiryDate, false),
            $row['status'] ?: 'รอส่งเอกสาร',
            $row['file_path'] ?: '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0B2F52']],
            ],
        ];
    }
}
