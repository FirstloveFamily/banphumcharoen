<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Resources\DocumentReviewResource;
use App\Filament\Staff\Resources\JobOrderResource;
use App\Filament\Staff\Resources\PaymentReviewResource;
use App\Models\JobOrder;
use App\Models\JobOrderChecklist;
use App\Models\JobOrderPayment;
use App\Models\Worker;
use App\Models\WorkerDocument;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'แดชบอร์ดเจ้าหน้าที่';

    protected static ?string $title = 'แดชบอร์ดเจ้าหน้าที่';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $view = 'filament.staff.pages.dashboard';

    public function getColumns(): int | string | array
    {
        return 1;
    }

    public function getStats(): array
    {
        $today = now()->startOfDay();
        $next30Days = $today->copy()->addDays(30)->endOfDay();

        return [
            [
                'label' => 'Passport ใกล้หมดอายุ',
                'value' => Worker::query()
                    ->active()
                    ->whereBetween('passport_expiry', [$today, $next30Days])
                    ->count(),
                'meta' => 'ภายใน 30 วัน',
                'icon' => 'heroicon-o-identification',
                'color' => 'amber',
            ],
            [
                'label' => 'เอกสารอื่นใกล้หมดอายุ',
                'value' => WorkerDocument::query()
                    ->whereBetween('expiry_date', [$today, $next30Days])
                    ->count(),
                'meta' => 'ภายใน 30 วัน',
                'icon' => 'heroicon-o-document-text',
                'color' => 'sky',
            ],
            [
                'label' => 'งานรอดำเนินการ',
                'value' => JobOrder::query()
                    ->whereIn('status', ['pending', 'processing', 'waiting_document', 'approved'])
                    ->count(),
                'meta' => 'ใบงานที่ยังไม่ปิด',
                'icon' => 'heroicon-o-clipboard-document-list',
                'color' => 'emerald',
            ],
            [
                'label' => 'สลิปรอตรวจ',
                'value' => JobOrderPayment::query()
                    ->where('status', 'pending')
                    ->count(),
                'meta' => 'รายการชำระเงิน',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'rose',
            ],
        ];
    }

    public function getExpiringItems(): Collection
    {
        $today = now()->startOfDay();
        $limit = $today->copy()->addDays(45)->endOfDay();

        $workerDates = Worker::query()
            ->with(['employer'])
            ->active()
            ->where(function ($query) use ($limit): void {
                $query
                    ->whereDate('passport_expiry', '<=', $limit)
                    ->orWhereDate('wp_expiry', '<=', $limit)
                    ->orWhereDate('visa_expiry', '<=', $limit)
                    ->orWhereDate('report_90_days_due', '<=', $limit);
            })
            ->limit(80)
            ->get()
            ->flatMap(function (Worker $worker): array {
                return collect([
                    ['type' => 'Passport', 'date' => $worker->passport_expiry],
                    ['type' => 'Work Permit', 'date' => $worker->wp_expiry],
                    ['type' => 'Visa', 'date' => $worker->visa_expiry],
                    ['type' => '90 Days Report', 'date' => $worker->report_90_days_due],
                ])
                    ->filter(fn (array $item): bool => filled($item['date']))
                    ->map(fn (array $item): array => [
                        'worker' => $worker->full_name_th ?: $worker->full_name_en,
                        'employer' => $worker->employer?->company_name ?? '-',
                        'document' => $item['type'],
                        'expiry_date' => $item['date'],
                        'source' => 'worker',
                    ])
                    ->all();
            });

        $documents = WorkerDocument::query()
            ->with(['worker.employer', 'documentMaster'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $limit)
            ->limit(80)
            ->get()
            ->map(fn (WorkerDocument $document): array => [
                'worker' => $document->worker?->full_name_th ?: $document->worker?->full_name_en ?: '-',
                'employer' => $document->worker?->employer?->company_name ?? '-',
                'document' => $document->documentMaster?->name ?? 'เอกสาร',
                'expiry_date' => $document->expiry_date,
                'source' => 'document',
            ]);

        return $workerDates
            ->merge($documents)
            ->filter(fn (array $item): bool => $item['expiry_date'] instanceof Carbon)
            ->sortBy('expiry_date')
            ->take(12)
            ->values();
    }

    public function getPendingDocumentReviews()
    {
        return JobOrderChecklist::query()
            ->with(['jobOrder.employer', 'jobOrder.worker', 'documentMaster'])
            ->whereIn('status', ['pending', 'received', 'missing', 'rejected'])
            ->latest()
            ->limit(6)
            ->get();
    }

    public function getQuickLinks(): array
    {
        return [
            [
                'label' => 'ใบงาน',
                'description' => 'ดูและอัปเดตสถานะงาน',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => JobOrderResource::getUrl('index'),
            ],
            [
                'label' => 'ตรวจเอกสาร',
                'description' => 'ตรวจไฟล์ที่นายจ้างส่งมา',
                'icon' => 'heroicon-o-document-check',
                'url' => DocumentReviewResource::getUrl('index'),
            ],
            [
                'label' => 'ตรวจสลิป',
                'description' => 'ยืนยันรายการชำระเงิน',
                'icon' => 'heroicon-o-banknotes',
                'url' => PaymentReviewResource::getUrl('index'),
            ],
        ];
    }

    public function getUrgencyColor(?Carbon $date): string
    {
        if (! $date) {
            return 'gray';
        }

        if ($date->isPast()) {
            return 'danger';
        }

        if ($date->diffInDays(now()) <= 14) {
            return 'warning';
        }

        return 'info';
    }

    public function getDaysLabel(?Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        if ($date->isPast()) {
            return 'หมดอายุแล้ว';
        }

        return 'อีก ' . (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay()) . ' วัน';
    }
}
