<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivityLogsWidget extends TableWidget
{
    // MUST be public, and MUST NOT have a type hint
    public $tableRecordsPerPage = 5;

    protected static ?string $heading = 'กิจกรรมล่าสุด';

    protected function getTableQuery(): Builder
    {
        return ActivityLog::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('action')
                ->label('การกระทำ')
                ->limit(20),
            TextColumn::make('model_type')
                ->label('โมเดล')
                ->limit(20),
            TextColumn::make('user.name')
                ->label('ผู้ใช้งาน')
                ->limit(18),
            TextColumn::make('created_at')
                ->label('วันที่')
                ->dateTime('d/m/Y H:i'),
        ];
    }
}