<?php

namespace App\Filament\Pages;

use App\Models\JobOrder;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class UnpaidReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'รายงานรายได้ค้างชำระ';
    protected static ?string $title = 'รายงานรายได้ค้างชำระ';
    protected static ?string $navigationGroup = 'รายงาน (Reports)';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.unpaid-report';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Pages\Widgets\UnpaidStatsOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JobOrder::query()
                    ->with(['employer', 'worker', 'service'])
                    ->whereIn('payment_status', ['pending', 'partial'])
            )
            ->columns([
                TextColumn::make('job_number')
                    ->label('เลขที่ใบงาน')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('employer.company_name')
                    ->label('ชื่อบริษัท/นายจ้าง')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('worker.full_name_th')
                    ->label('ชื่อแรงงาน')
                    ->searchable(['first_name_th', 'last_name_th', 'first_name_en', 'last_name_en'])
                    ->sortable(['first_name_th']),

                TextColumn::make('service.name')
                    ->label('บริการ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_fee')
                    ->label('ค่าบริการรวม')
                    ->money('thb')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('ชำระแล้ว')
                    ->money('thb')
                    ->sortable(),

                TextColumn::make('remaining')
                    ->label('ยอดค้างชำระ')
                    ->money('thb')
                    ->state(fn (JobOrder $record): float => (float)$record->service_fee - (float)$record->paid_amount)
                    ->color('danger')
                    ->weight('bold'),

                TextColumn::make('due_date')
                    ->label('กำหนดชำระ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'gray'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('created_year')
                    ->label('ปี (Year)')
                    ->options(function () {
                        $years = \App\Models\JobOrder::select('created_at')
                            ->pluck('created_at')
                            ->map(fn ($date) => $date->format('Y'))
                            ->unique()
                            ->sortDesc()
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray();
                            
                        $currentYear = date('Y');
                        if (!isset($years[$currentYear])) {
                            $years[$currentYear] = $currentYear;
                        }
                        return $years;
                    })
                    ->default(date('Y'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value']) {
                            return $query->whereYear('created_at', $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->defaultSort('due_date', 'asc');
    }
}
