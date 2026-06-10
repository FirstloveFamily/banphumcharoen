<?php

namespace App\Filament\Pages\Widgets;

use App\Models\JobOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnpaidStatsOverview extends BaseWidget
{
    use \Filament\Widgets\Concerns\InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return \App\Filament\Pages\UnpaidReport::class;
    }

    protected function getStats(): array
    {
        // ดึงค่าปีจากตัวกรองของตาราง หากไม่มีให้ใช้ปีปัจจุบัน
        $year = $this->tableFilters['created_year']['value'] ?? date('Y');
        
        // ดึงเฉพาะของปีที่เลือก
        $allJobsThisYear = JobOrder::whereYear('created_at', $year)->get();
        
        // คำนวณรายได้ทั้งหมดที่คาดหวัง และที่รับชำระแล้ว (กำไรสุทธิ)
        $totalExpected = $allJobsThisYear->sum('service_fee');
        $totalCollected = $allJobsThisYear->sum('paid_amount');
        
        // คำนวณยอดค้างชำระ
        $unpaidJobs = $allJobsThisYear->filter(fn ($job) => in_array($job->payment_status, ['pending', 'partial']));
        $totalUnpaidAmount = $totalExpected - $totalCollected;
        
        // ค้างชำระเกินกำหนด
        $overdueJobs = $unpaidJobs->filter(fn ($job) => $job->due_date && $job->due_date->isPast());
        $overdueAmount = $overdueJobs->sum(fn ($job) => $job->service_fee - $job->paid_amount);

        return [
            Stat::make("รายรับสุทธิ (ปี $year)", number_format($totalCollected, 2) . ' ฿')
                ->description('ยอดเงินที่เก็บได้แล้วทั้งหมดในปีนี้')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make("ยอดค้างชำระ (ปี $year)", number_format($totalUnpaidAmount, 2) . ' ฿')
                ->description($unpaidJobs->count() . ' รายการที่ยังค้างชำระ')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($totalUnpaidAmount > 0 ? 'warning' : 'success'),

            Stat::make('ค้างชำระเกินกำหนด', number_format($overdueAmount, 2) . ' ฿')
                ->description($overdueJobs->count() . ' รายการที่เลยกำหนดชำระ')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueAmount > 0 ? 'danger' : 'success'),
        ];
    }
}
