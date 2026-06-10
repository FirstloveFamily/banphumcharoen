<?php

namespace App\Filament\Pages;

use App\Models\Worker;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ExpiringReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'รายงานใกล้หมดอายุ';
    protected static ?string $title = 'รายงานรายการใกล้หมดอายุ';
    protected static ?string $navigationGroup = 'รายงาน (Reports)';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.expiring-report';

    public function getExpiringWorkers()
    {
        return Worker::query()
            ->with('employer')
            ->where(function (Builder $query) {
                $query->whereNotNull('passport_expiry')->where('passport_expiry', '<=', now()->addDays(30))
                    ->orWhereNotNull('wp_expiry')->where('wp_expiry', '<=', now()->addDays(30))
                    ->orWhereNotNull('visa_expiry')->where('visa_expiry', '<=', now()->addDays(30))
                    ->orWhereNotNull('report_90_days_due')->where('report_90_days_due', '<=', now()->addDays(30));
            })
            ->orderBy('passport_expiry')
            ->get();
    }

    public function getStats()
    {
        return [
            'expired' => Worker::query()
                ->where(function (Builder $query) {
                    $query->whereNotNull('passport_expiry')->whereDate('passport_expiry', '<', now())
                        ->orWhereNotNull('wp_expiry')->whereDate('wp_expiry', '<', now())
                        ->orWhereNotNull('visa_expiry')->whereDate('visa_expiry', '<', now())
                        ->orWhereNotNull('report_90_days_due')->whereDate('report_90_days_due', '<', now());
                })
                ->count(),
            'expiring_7_days' => Worker::query()
                ->where(function (Builder $query) {
                    $query->whereNotNull('passport_expiry')->whereBetween('passport_expiry', [now(), now()->addDays(7)])
                        ->orWhereNotNull('wp_expiry')->whereBetween('wp_expiry', [now(), now()->addDays(7)])
                        ->orWhereNotNull('visa_expiry')->whereBetween('visa_expiry', [now(), now()->addDays(7)])
                        ->orWhereNotNull('report_90_days_due')->whereBetween('report_90_days_due', [now(), now()->addDays(7)]);
                })
                ->count(),
            'expiring_30_days' => Worker::query()
                ->where(function (Builder $query) {
                    $query->whereNotNull('passport_expiry')->whereBetween('passport_expiry', [now(), now()->addDays(30)])
                        ->orWhereNotNull('wp_expiry')->whereBetween('wp_expiry', [now(), now()->addDays(30)])
                        ->orWhereNotNull('visa_expiry')->whereBetween('visa_expiry', [now(), now()->addDays(30)])
                        ->orWhereNotNull('report_90_days_due')->whereBetween('report_90_days_due', [now(), now()->addDays(30)]);
                })
                ->count(),
        ];
    }

    public function formatDate(?Carbon $date): string
    {
        return $date?->format('d/m/Y') ?? '-';
    }

    public function getColorForDate($date): string
    {
        if (!$date) return 'gray';

        $carbonDate = Carbon::parse($date);

        if ($carbonDate->isPast()) {
            return 'danger';
        }

        if ($carbonDate->diffInDays(now()) <= 7) {
            return 'danger';
        }

        if ($carbonDate->diffInDays(now()) <= 30) {
            return 'warning';
        }

        return 'success';
    }
}
