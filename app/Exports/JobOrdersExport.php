<?php

namespace App\Exports;

use App\Models\JobOrder;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JobOrdersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $keyword = '',
        private readonly string $status = '',
        private readonly string $priority = '',
        private readonly string $paymentStatus = '',
    ) {
    }

    public function query(): Builder
    {
        return JobOrder::query()
            ->with(['employer', 'worker', 'service', 'assignedUser', 'statusDefinition'])
            ->withCount([
                'checklists as pending_documents_count' => fn ($query) => $query->whereIn('status', ['pending', 'missing', 'rejected']),
                'payments as pending_payments_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->when($this->keyword !== '', function (Builder $query): void {
                $query->where(function (Builder $subQuery): void {
                    $subQuery
                        ->where('job_number', 'like', "%{$this->keyword}%")
                        ->orWhereHas('employer', fn (Builder $employerQuery) => $employerQuery->where('company_name', 'like', "%{$this->keyword}%"))
                        ->orWhereHas('worker', function (Builder $workerQuery): void {
                            $workerQuery
                                ->where('first_name_th', 'like', "%{$this->keyword}%")
                                ->orWhere('last_name_th', 'like', "%{$this->keyword}%")
                                ->orWhere('first_name_en', 'like', "%{$this->keyword}%")
                                ->orWhere('last_name_en', 'like', "%{$this->keyword}%");
                        });
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->priority !== '', fn (Builder $query) => $query->where('priority', $this->priority))
            ->when($this->paymentStatus !== '', fn (Builder $query) => $query->where('payment_status', $this->paymentStatus))
            ->orderByRaw('case when due_date is null then 1 else 0 end')
            ->orderBy('due_date', 'asc')
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->latest('updated_at');
    }

    public function headings(): array
    {
        return [
            'เลขใบงาน',
            'กำหนดเสร็จ',
            'แรงงาน',
            'บริษัทนายจ้าง',
            'ประเภทบริการ',
            'ผู้รับผิดชอบ',
            'สถานะงาน',
            'ความสำคัญ',
            'สถานะชำระเงิน',
            'ค่าบริการ',
            'ชำระแล้ว',
            'ยอดคงเหลือ',
            'เอกสารค้างตรวจ',
            'สลิปค้างตรวจ',
            'หมายเหตุ',
            'สร้างเมื่อ',
            'อัปเดตล่าสุด',
        ];
    }

    public function map($jobOrder): array
    {
        $priorityLabels = [
            'urgent' => 'ด่วนพิเศษ',
            'high' => 'สูง',
            'medium' => 'ปานกลาง',
            'low' => 'ต่ำ',
        ];
        $paymentLabels = [
            'pending' => 'รอชำระ',
            'partial' => 'ชำระบางส่วน',
            'paid' => 'ชำระครบ',
            'cancelled' => 'ยกเลิก',
        ];

        return [
            $jobOrder->job_number,
            $jobOrder->due_date?->format('d/m/Y') ?? '-',
            $jobOrder->worker?->full_name_th ?: ($jobOrder->worker?->full_name_en ?: '-'),
            $jobOrder->employer?->company_name ?: '-',
            $jobOrder->service?->name ?: '-',
            $jobOrder->assignedUser?->name ?: '-',
            $jobOrder->status_label,
            $priorityLabels[$jobOrder->priority] ?? $jobOrder->priority,
            $paymentLabels[$jobOrder->payment_status] ?? $jobOrder->payment_status,
            (float) $jobOrder->service_fee,
            (float) $jobOrder->paid_amount,
            $jobOrder->getRemainingAmount(),
            (int) $jobOrder->pending_documents_count,
            (int) $jobOrder->pending_payments_count,
            $jobOrder->notes ?: '-',
            $jobOrder->created_at?->format('d/m/Y H:i') ?? '-',
            $jobOrder->updated_at?->format('d/m/Y H:i') ?? '-',
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
