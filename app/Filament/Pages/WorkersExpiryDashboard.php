<?php

namespace App\Filament\Pages;

use App\Models\Worker;
use App\Models\WorkerDocument;
use App\Models\ActivityLog;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class WorkersExpiryDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'แดชบอร์ด: เอกสารแรงงาน';
    protected static ?string $title = 'แดชบอร์ดเอกสารแรงงาน';
    protected static ?string $navigationGroup = 'แดชบอร์ด (Admin)';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.workers-expiry-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('view workers-expiry-dashboard');
    }

    // Match Livewire Component::authorize signature
    public function authorize($ability = null, $arguments = [])
    {
        return auth()->check() && auth()->user()->can('view workers-expiry-dashboard');
    }

    public function getKpis(): array
    {
        $today = now();

        return [
            ['label' => 'เอกสารหมดอายุ', 'value' => Worker::query()->where(function (Builder $q) use ($today) {
                $q->whereNotNull('passport_expiry')->whereDate('passport_expiry', '<', $today)
                    ->orWhereNotNull('wp_expiry')->whereDate('wp_expiry', '<', $today)
                    ->orWhereNotNull('visa_expiry')->whereDate('visa_expiry', '<', $today);
            })->count(), 'color' => 'rose'],
            ['label' => 'ใกล้หมด (7 วัน)', 'value' => Worker::query()->where(function (Builder $q) use ($today) {
                $q->whereNotNull('passport_expiry')->whereBetween('passport_expiry', [$today, $today->copy()->addDays(7)])
                    ->orWhereNotNull('wp_expiry')->whereBetween('wp_expiry', [$today, $today->copy()->addDays(7)])
                    ->orWhereNotNull('visa_expiry')->whereBetween('visa_expiry', [$today, $today->copy()->addDays(7)]);
            })->count(), 'color' => 'amber'],
            ['label' => 'ใกล้หมด (30 วัน)', 'value' => Worker::query()->where(function (Builder $q) use ($today) {
                $q->whereNotNull('passport_expiry')->whereBetween('passport_expiry', [$today, $today->copy()->addDays(30)])
                    ->orWhereNotNull('wp_expiry')->whereBetween('wp_expiry', [$today, $today->copy()->addDays(30)])
                    ->orWhereNotNull('visa_expiry')->whereBetween('visa_expiry', [$today, $today->copy()->addDays(30)]);
            })->count(), 'color' => 'sky'],
            ['label' => 'แรงงานทั้งหมด', 'value' => Worker::count(), 'color' => 'emerald'],
        ];
    }

    public function getExpiringDocuments(int $limit = 10)
    {
        return WorkerDocument::query()
            ->with(['worker', 'documentMaster'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(45))
            ->orderBy('expiry_date')
            ->limit($limit)
            ->get();
    }

    public function formatDate(?Carbon $date): string
    {
        return $date?->format('d M Y') ?? '-';
    }

    public function getColorForDate($date): string
    {
        if (!$date) return 'gray';
        $carbon = Carbon::parse($date);
        if ($carbon->isPast()) return 'red';
        if ($carbon->diffInDays(now()) <= 7) return 'amber';
        if ($carbon->diffInDays(now()) <= 30) return 'blue';
        return 'emerald';
    }

    public function getRecentActivities(int $limit = 8)
    {
        return ActivityLog::query()
            ->with('user')
            ->recent(30)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRiskStats(int $limit = 200): array
    {
        $docs = $this->getExpiringDocuments($limit);
        $today = now();

        $expiredCount = $docs->filter(fn($d) => $d->expiry_date && Carbon::parse($d->expiry_date)->isPast())->count();
        $within7 = $docs->filter(fn($d) => $d->expiry_date && Carbon::parse($d->expiry_date)->between($today, $today->copy()->addDays(7)))->count();
        $within30 = $docs->filter(fn($d) => $d->expiry_date && Carbon::parse($d->expiry_date)->between($today, $today->copy()->addDays(30)))->count();
        $totalDocs = max(1, $docs->count());
        $percentAtRisk = (int) round((($expiredCount + $within7 + $within30) / $totalDocs) * 100);
        $groups = $docs->groupBy(fn($d) => $d->documentMaster?->name ?? 'อื่นๆ');

        return compact('expiredCount', 'within7', 'within30', 'totalDocs', 'percentAtRisk', 'groups');
    }

    public static function streamExpiringDocsCsv(int $days = 45): StreamedResponse
    {
        $callback = function () use ($days) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Document', 'Worker', 'Nationality', 'Employer', 'Document Number', 'Expiry Date', 'Days Left']);

            \App\Models\WorkerDocument::query()
                ->with(['worker', 'documentMaster', 'worker.employer', 'worker.nationality'])
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays($days))
                ->orderBy('expiry_date')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $r) {
                        $daysLeft = $r->expiry_date ? Carbon::parse($r->expiry_date)->diffInDays(now()) : '';
                        fputcsv($handle, [
                            $r->documentMaster?->name ?? '',
                            $r->worker?->full_name_th ?: $r->worker?->full_name_en ?: '',
                            $r->worker?->nationality?->name_th ?? '',
                            $r->worker?->employer?->company_name ?? '',
                            $r->document_number ?? '',
                            $r->expiry_date?->format('Y-m-d') ?? '',
                            $daysLeft,
                        ]);
                    }
                });

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="expiring-docs.csv"',
        ]);
    }

    public static function notifyExpiringDocs(): bool
    {
        try {
            \App\Models\ActivityLog::create([
                'user_id' => null,
                'action' => 'notify_expiring_docs',
                'model_type' => 'WorkerDocument',
                'model_id' => null,
                'description' => 'ส่งการแจ้งเตือนไปยังนายจ้าง/เจ้าหน้าที่เกี่ยวกับเอกสารที่ใกล้หมดอายุ',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('NotifyExpiringDocs failed: ' . $e->getMessage());
            return false;
        }
    }
}
