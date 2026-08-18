<?php

namespace App\Exports;

use App\Models\Worker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $keyword = '',
        private readonly string $expiryStatus = '',
        private readonly string $documentExpiry = '',
        private readonly string $activeStatus = '',
        private readonly string $sort = 'name',
    ) {
    }

    public function query(): Builder
    {
        $today = now()->startOfDay();
        $soon = now()->copy()->addDays(45)->endOfDay();

        $query = Worker::query()
            ->with(['employer', 'documents.documentMaster'])
            ->when($this->keyword !== '', function (Builder $query): void {
                $query->where(function (Builder $subQuery): void {
                    $subQuery
                        ->where('first_name_th', 'like', "%{$this->keyword}%")
                        ->orWhere('last_name_th', 'like', "%{$this->keyword}%")
                        ->orWhere('first_name_en', 'like', "%{$this->keyword}%")
                        ->orWhere('last_name_en', 'like', "%{$this->keyword}%")
                        ->orWhere('passport_number', 'like', "%{$this->keyword}%")
                        ->orWhere('pink_card_number', 'like', "%{$this->keyword}%")
                        ->orWhere('wp_number', 'like', "%{$this->keyword}%")
                        ->orWhereHas('employer', fn (Builder $employerQuery) => $employerQuery
                            ->where('company_name', 'like', "%{$this->keyword}%"));
                });
            })
            ->when($this->expiryStatus === 'expiring', function (Builder $query) use ($today, $soon): void {
                $query->where(function (Builder $subQuery) use ($today, $soon): void {
                    $subQuery
                        ->whereBetween('wp_expiry', [$today, $soon])
                        ->orWhereBetween('visa_expiry', [$today, $soon])
                        ->orWhereBetween('passport_expiry', [$today, $soon])
                        ->orWhereBetween('pink_card_expiry', [$today, $soon])
                        ->orWhereBetween('report_90_days_due', [$today, $soon]);
                });
            })
            ->when($this->expiryStatus === 'expired', function (Builder $query) use ($today): void {
                $query->where(function (Builder $subQuery) use ($today): void {
                    $subQuery
                        ->whereDate('wp_expiry', '<', $today)
                        ->orWhereDate('visa_expiry', '<', $today)
                        ->orWhereDate('passport_expiry', '<', $today)
                        ->orWhereDate('pink_card_expiry', '<', $today)
                        ->orWhereDate('report_90_days_due', '<', $today);
                });
            })
            ->when($this->documentExpiry !== '', function (Builder $query) use ($today): void {
                $legacyColumns = [
                    'passport' => 'passport_expiry',
                    'work_permit' => 'wp_expiry',
                    'visa' => 'visa_expiry',
                    'report_90' => 'report_90_days_due',
                ];

                if (isset($legacyColumns[$this->documentExpiry])) {
                    $query->whereDate($legacyColumns[$this->documentExpiry], '<', $today);
                } elseif ($this->documentExpiry === 'pink_card') {
                    $query->where(function (Builder $subQuery) use ($today): void {
                        $subQuery->whereDate('pink_card_expiry', '<', $today)
                            ->orWhereHas('documents', function (Builder $documentQuery) use ($today): void {
                                $documentQuery
                                    ->whereDate('expiry_date', '<', $today)
                                    ->whereHas('documentMaster', fn (Builder $masterQuery) => $masterQuery
                                        ->where('id', 12)
                                        ->orWhere('name', 'บัตรชมพู')
                                        ->orWhere('code', 'Pink Identification Card for Foreign Workers'));
                            });
                    });
                }
            })
            ->when($this->activeStatus === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($this->activeStatus === 'inactive', fn (Builder $query) => $query->where('is_active', false));

        if ($this->sort === 'nearest_expiry') {
            $columns = ['passport_expiry', 'pink_card_expiry', 'wp_expiry', 'visa_expiry', 'report_90_days_due'];
            $parts = collect($columns)
                ->map(fn (string $column): string => "COALESCE({$column}, '9999-12-31')")
                ->implode(', ');
            $expression = DB::connection()->getDriverName() === 'mysql'
                ? "LEAST({$parts})"
                : "min({$parts})";

            $query->orderByRaw("{$expression} asc");
        } elseif (in_array($this->sort, ['passport_expiry', 'pink_card_expiry', 'wp_expiry', 'visa_expiry', 'report_90_days_due'], true)) {
            $query->orderByRaw("{$this->sort} is null")->orderBy($this->sort);
        } else {
            $query->orderByDesc('is_active')->orderBy('first_name_th');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ชื่อแรงงาน (ไทย)',
            'ชื่อแรงงาน (อังกฤษ)',
            'บริษัทนายจ้าง',
            'สถานะแรงงาน',
            'เลขที่ Passport',
            'วันหมดอายุ Passport',
            'สถานะ Passport',
            'เลขบัตรชมพู',
            'วันหมดอายุบัตรชมพู',
            'สถานะบัตรชมพู',
            'เลขที่ Work Permit',
            'วันหมดอายุ Work Permit',
            'สถานะ Work Permit',
            'วันหมดอายุ Visa',
            'สถานะ Visa',
            'วันครบกำหนด 90 วัน',
            'สถานะ 90 วัน',
        ];
    }

    public function map($worker): array
    {
        return $this->mapLegacyFields($worker, $this->pinkCardDocument($worker));
    }

    private function pinkCardDocument($worker)
    {
        return $worker->documents->first(fn ($item) =>
            $item->document_master_id === 12
            || $item->documentMaster?->name === 'บัตรชมพู'
            || $item->documentMaster?->code === 'Pink Identification Card for Foreign Workers'
        );
    }

    private function mapLegacyFields($worker, $pinkCard): array
    {
        return [
            $worker->full_name_th ?: '-',
            $worker->full_name_en ?: '-',
            $worker->employer?->company_name ?: '-',
            $worker->is_active ? 'Active' : 'Inactive',
            $worker->passport_number ?: '-',
            $this->formatDate($worker->passport_expiry),
            $this->dateStatus($worker->passport_expiry),
            $worker->pink_card_number ?: '-',
            $this->formatDate($worker->pink_card_expiry ?: $pinkCard?->expiry_date),
            $worker->pink_card_status
                ? $this->workflowStatus((object) ['status' => $worker->pink_card_status])
                : $this->workflowStatus($pinkCard),
            $worker->wp_number ?: '-',
            $this->formatDate($worker->wp_expiry),
            $this->dateStatus($worker->wp_expiry),
            $this->formatDate($worker->visa_expiry),
            $this->dateStatus($worker->visa_expiry),
            $this->formatDate($worker->report_90_days_due),
            $this->dateStatus($worker->report_90_days_due),
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

    private function formatDate(?Carbon $date): string
    {
        return $date?->format('d/m/Y') ?? '-';
    }

    private function dateStatus(?Carbon $date): string
    {
        if (! $date) {
            return 'ไม่มีข้อมูล';
        }

        $days = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

        return match (true) {
            $days < 0 => 'หมดอายุ',
            $days <= 45 => 'ใกล้หมดอายุ',
            default => 'ปกติ',
        };
    }

    private function workflowStatus($document): string
    {
        return match ($document?->status) {
            'approved' => 'ผ่านแล้ว',
            'processing' => 'กำลังดำเนินการ',
            'rejected' => 'ถูกตีกลับ',
            'pending_review' => 'รอตรวจสอบ',
            default => 'รอส่งเอกสาร',
        };
    }
}
