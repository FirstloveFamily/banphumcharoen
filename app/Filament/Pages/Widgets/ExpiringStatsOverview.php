<?php

namespace App\Filament\Pages\Widgets;

use App\Models\Worker;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpiringStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $nearExpiryDays = 30;
        
        $passportNearExpiry = Worker::whereNotNull('passport_expiry')
            ->where('passport_expiry', '<=', now()->addDays($nearExpiryDays))
            ->count();
            
        $wpNearExpiry = Worker::whereNotNull('wp_expiry')
            ->where('wp_expiry', '<=', now()->addDays($nearExpiryDays))
            ->count();
            
        $visaNearExpiry = Worker::whereNotNull('visa_expiry')
            ->where('visa_expiry', '<=', now()->addDays($nearExpiryDays))
            ->count();
            
        $report90NearExpiry = Worker::whereNotNull('report_90_days_due')
            ->where('report_90_days_due', '<=', now()->addDays($nearExpiryDays))
            ->count();

        return [
            Stat::make('พาสปอร์ตใกล้หมด', $passportNearExpiry)
                ->description('ภายใน 30 วัน หรือหมดแล้ว')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($passportNearExpiry > 0 ? 'danger' : 'success'),

            Stat::make('Work Permit ใกล้หมด', $wpNearExpiry)
                ->description('ภายใน 30 วัน หรือหมดแล้ว')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($wpNearExpiry > 0 ? 'danger' : 'success'),

            Stat::make('Visa ใกล้หมด', $visaNearExpiry)
                ->description('ภายใน 30 วัน หรือหมดแล้ว')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($visaNearExpiry > 0 ? 'danger' : 'success'),
                
            Stat::make('รายงาน 90 วัน', $report90NearExpiry)
                ->description('กำหนดภายใน 30 วัน')
                ->descriptionIcon('heroicon-m-clock')
                ->color($report90NearExpiry > 0 ? 'warning' : 'success'),
        ];
    }
}
