<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Employer;
use App\Models\JobOrder;
use App\Models\JobOrderPayment;
use App\Models\Notification;
use App\Models\Worker;
use App\Models\WorkerDocument;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'HR Operations Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $view = 'filament.pages.dashboard';

    public function getColumns(): int | string | array
    {
        return 1;
    }

    public function getKpis(): array
    {
        $today = now();
        $startOfMonth = $today->copy()->startOfMonth();
        $previousMonthStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $today->copy()->subMonthNoOverflow()->endOfMonth();

        return [
            [
                'label' => 'Active Workers',
                'value' => number_format(Worker::query()->active()->count()),
                'meta' => number_format(Worker::query()->whereBetween('created_at', [$startOfMonth, $today])->count()) . ' added this month',
                'icon' => 'heroicon-o-identification',
                'color' => 'emerald',
            ],
            [
                'label' => 'Open Job Orders',
                'value' => number_format(JobOrder::query()->whereNotIn('status', ['completed', 'cancelled', 'rejected'])->count()),
                'meta' => number_format(JobOrder::query()->where('priority', 'urgent')->whereNotIn('status', ['completed', 'cancelled', 'rejected'])->count()) . ' urgent',
                'icon' => 'heroicon-o-briefcase',
                'color' => 'amber',
            ],
            [
                'label' => 'Monthly Revenue',
                'value' => '฿' . number_format((float) JobOrderPayment::query()->verified()->whereBetween('payment_date', [$startOfMonth, $today])->sum('amount'), 0),
                'meta' => 'Prev ฿' . number_format((float) JobOrderPayment::query()->verified()->whereBetween('payment_date', [$previousMonthStart, $previousMonthEnd])->sum('amount'), 0),
                'icon' => 'heroicon-o-banknotes',
                'color' => 'sky',
            ],
            [
                'label' => 'Unread Alerts',
                'value' => number_format(Notification::query()->where('is_read', false)->count()),
                'meta' => number_format(WorkerDocument::query()->whereNotNull('expiry_date')->whereBetween('expiry_date', [$today, $today->copy()->addDays(30)])->count()) . ' docs expiring',
                'icon' => 'heroicon-o-bell-alert',
                'color' => 'rose',
            ],
        ];
    }

    public function getPipeline(): array
    {
        $statuses = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'waiting_document' => 'Waiting Docs',
            'approved' => 'Approved',
            'completed' => 'Completed',
        ];

        $counts = JobOrder::query()
            ->selectRaw('status, count(*) as aggregate')
            ->whereIn('status', array_keys($statuses))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $max = max((int) $counts->max(), 1);

        return collect($statuses)
            ->map(fn (string $label, string $status): array => [
                'label' => $label,
                'count' => (int) ($counts[$status] ?? 0),
                'width' => ((int) ($counts[$status] ?? 0) / $max) * 100,
            ])
            ->values()
            ->all();
    }

    public function getUrgentJobOrders()
    {
        return JobOrder::query()
            ->with(['employer', 'worker', 'service'])
            ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->orderBy('due_date')
            ->limit(6)
            ->get();
    }

    public function getExpiringDocuments()
    {
        return WorkerDocument::query()
            ->with(['worker', 'documentMaster'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(45))
            ->orderBy('expiry_date')
            ->limit(6)
            ->get();
    }

    public function getRecentActivities()
    {
        return ActivityLog::query()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();
    }

    public function getTopEmployers()
    {
        return Employer::query()
            ->withCount(['workers', 'jobOrders'])
            ->orderByDesc('job_orders_count')
            ->limit(5)
            ->get();
    }

    public function getOperationsSummary(): array
    {
        $today = now();

        return [
            'completionRate' => $this->percentage(
                JobOrder::query()->where('status', 'completed')->count(),
                JobOrder::query()->count()
            ),
            'paymentCollection' => $this->percentage(
                (float) JobOrder::query()->sum('paid_amount'),
                (float) JobOrder::query()->sum('service_fee')
            ),
            'activeEmployers' => Employer::query()->active()->count(),
            'overdueOrders' => JobOrder::query()
                ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
                ->whereDate('due_date', '<', $today)
                ->count(),
        ];
    }

    public function formatDate(?Carbon $date): string
    {
        return $date?->format('d M Y') ?? '-';
    }

    private function percentage(float|int $part, float|int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($part / $total) * 100);
    }
}
