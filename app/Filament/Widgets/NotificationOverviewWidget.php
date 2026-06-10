<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NotificationOverviewWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'สรุปการแจ้งเตือน';

    protected function getStats(): array
    {
        return [
            Stat::make('แจ้งเตือนใหม่', Notification::query()->where('is_read', false)->count())
                ->color('warning')
                ->icon('heroicon-s-bell')
                ->description('ยังไม่ได้อ่าน'),

            Stat::make('แจ้งเตือนทั้งหมด', Notification::query()->count())
                ->color('primary')
                ->icon('heroicon-s-bell')
                ->description('รวมการแจ้งเตือนทั้งหมด'),
        ];
    }
}
