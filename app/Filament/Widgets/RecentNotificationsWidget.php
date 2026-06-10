<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentNotificationsWidget extends TableWidget
{
    // MUST be public, and MUST NOT have a type hint
    public $tableRecordsPerPage = 5;

    protected static ?string $heading = 'การแจ้งเตือนล่าสุด';

    protected function getTableQuery(): Builder
    {
        return Notification::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            // Updated to the Filament v3 syntax
            Tables\Columns\TextColumn::make('is_read')
                ->label('สถานะ')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'อ่านแล้ว' : 'ยังไม่อ่าน')
                ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
                
            TextColumn::make('title')
                ->label('หัวข้อ')
                ->limit(24),
            TextColumn::make('user.name')
                ->label('ผู้ใช้งาน')
                ->limit(18),
            TextColumn::make('created_at')
                ->label('วันที่')
                ->dateTime('d/m/Y H:i'),
        ];
    }
}