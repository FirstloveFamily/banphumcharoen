<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\JobOrderResource\Pages;
use App\Models\JobOrder;
use App\Models\JobOrderLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class JobOrderResource extends Resource
{
    protected static ?string $model = JobOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'ใบงาน';

    protected static ?string $navigationGroup = 'งานปฏิบัติการ';

    protected static ?string $modelLabel = 'ใบงาน';

    protected static ?string $pluralModelLabel = 'ใบงาน';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['employer', 'worker', 'service', 'assignedUser'])
            ->whereNotIn('status', ['cancelled']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ข้อมูลใบงาน')
                    ->schema([
                        Forms\Components\Placeholder::make('job_number')
                            ->label('เลขงาน')
                            ->content(fn (?JobOrder $record): string => $record?->job_number ?? '-'),
                        Forms\Components\Placeholder::make('employer')
                            ->label('นายจ้าง')
                            ->content(fn (?JobOrder $record): string => $record?->employer?->company_name ?? '-'),
                        Forms\Components\Placeholder::make('worker')
                            ->label('แรงงาน')
                            ->content(fn (?JobOrder $record): string => $record?->worker?->full_name_th ?: $record?->worker?->full_name_en ?: '-'),
                        Forms\Components\Placeholder::make('service')
                            ->label('บริการ')
                            ->content(fn (?JobOrder $record): string => $record?->service?->name ?? '-'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('อัปเดตงาน')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('สถานะงาน')
                            ->required()
                            ->options([
                                'pending' => 'รอเริ่มงาน',
                                'processing' => 'กำลังดำเนินการ',
                                'waiting_document' => 'รอเอกสาร',
                                'approved' => 'อนุมัติแล้ว',
                                'completed' => 'เสร็จสิ้น',
                                'rejected' => 'ไม่ผ่าน',
                            ]),
                        Forms\Components\Select::make('priority')
                            ->label('ความสำคัญ')
                            ->required()
                            ->options([
                                'low' => 'ต่ำ',
                                'medium' => 'ปานกลาง',
                                'high' => 'สูง',
                                'urgent' => 'ด่วน',
                            ]),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('กำหนดเสร็จ'),
                        Forms\Components\Textarea::make('notes')
                            ->label('หมายเหตุภายใน')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_number')
                    ->label('เลขงาน')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('employer.company_name')
                    ->label('นายจ้าง')
                    ->searchable()
                    ->limit(28),
                Tables\Columns\TextColumn::make('worker.full_name_th')
                    ->label('แรงงาน')
                    ->searchable(['first_name_th', 'last_name_th', 'first_name_en', 'last_name_en'])
                    ->limit(28),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('บริการ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอเริ่มงาน',
                        'processing' => 'กำลังดำเนินการ',
                        'waiting_document' => 'รอเอกสาร',
                        'approved' => 'อนุมัติแล้ว',
                        'completed' => 'เสร็จสิ้น',
                        'rejected' => 'ไม่ผ่าน',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'waiting_document' => 'info',
                        'approved' => 'success',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('ความสำคัญ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('กำหนดเสร็จ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดต')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอเริ่มงาน',
                        'processing' => 'กำลังดำเนินการ',
                        'waiting_document' => 'รอเอกสาร',
                        'approved' => 'อนุมัติแล้ว',
                        'completed' => 'เสร็จสิ้น',
                        'rejected' => 'ไม่ผ่าน',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label('เริ่มงาน')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn (JobOrder $record): bool => $record->status === 'pending')
                    ->action(function (JobOrder $record): void {
                        $record->update([
                            'status' => 'processing',
                            'assigned_user_id' => $record->assigned_user_id ?: Auth::id(),
                            'started_at' => $record->started_at ?: now(),
                        ]);

                        JobOrderLog::create([
                            'job_order_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'เจ้าหน้าที่เริ่มดำเนินงาน',
                            'description' => 'เริ่มดำเนินงานโดยเจ้าหน้าที่',
                        ]);
                    }),
                Tables\Actions\EditAction::make()
                    ->label('อัปเดต'),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobOrders::route('/'),
            'edit' => Pages\EditJobOrder::route('/{record}/edit'),
        ];
    }
}
