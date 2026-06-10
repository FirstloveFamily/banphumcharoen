<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\PaymentReviewResource\Pages;
use App\Models\JobOrderLog;
use App\Models\JobOrderPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentReviewResource extends Resource
{
    protected static ?string $model = JobOrderPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'ตรวจสลิป';

    protected static ?string $navigationGroup = 'งานปฏิบัติการ';

    protected static ?string $modelLabel = 'รายการตรวจสลิป';

    protected static ?string $pluralModelLabel = 'รายการตรวจสลิป';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['jobOrder.employer', 'jobOrder.worker', 'receiver'])
            ->latest('payment_date');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ผลการตรวจสลิป')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('สถานะ')
                            ->options([
                                'pending' => 'รอตรวจสอบ',
                                'verified' => 'ตรวจสอบแล้ว',
                                'rejected' => 'ถูกปฏิเสธ',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label('หมายเหตุ')
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
                Tables\Columns\TextColumn::make('jobOrder.job_number')
                    ->label('เลขงาน')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('jobOrder.employer.company_name')
                    ->label('นายจ้าง')
                    ->searchable()
                    ->limit(28),
                Tables\Columns\TextColumn::make('amount')
                    ->label('ยอดโอน')
                    ->money('THB')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('slip_path')
                    ->label('สลิป')
                    ->disk('public')
                    ->square()
                    ->size(56)
                    ->url(fn (?string $state): ?string => $state ? asset('storage/' . $state) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอตรวจสอบ',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('วันที่ชำระ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('ตรวจโดย')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอตรวจสอบ',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->label('ตรวจผ่าน')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (JobOrderPayment $record): bool => $record->status !== 'verified')
                    ->action(function (JobOrderPayment $record): void {
                        $record->update([
                            'status' => 'verified',
                            'received_by' => Auth::id(),
                        ]);

                        $record->jobOrder?->syncPaymentSummary();

                        if ($record->jobOrder) {
                            JobOrderLog::create([
                                'job_order_id' => $record->jobOrder->id,
                                'user_id' => Auth::id(),
                                'action' => 'ตรวจสลิปผ่าน',
                                'description' => 'ตรวจสอบยอดชำระ ' . number_format((float) $record->amount, 2) . ' บาท ผ่านแล้ว',
                            ]);
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('ปฏิเสธ')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('เหตุผล')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (JobOrderPayment $record): bool => $record->status !== 'rejected')
                    ->action(function (JobOrderPayment $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'received_by' => Auth::id(),
                            'note' => $data['note'],
                        ]);

                        $record->jobOrder?->syncPaymentSummary();

                        if ($record->jobOrder) {
                            JobOrderLog::create([
                                'job_order_id' => $record->jobOrder->id,
                                'user_id' => Auth::id(),
                                'action' => 'สลิปไม่ผ่าน',
                                'description' => $data['note'],
                            ]);
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->label('แก้สถานะ'),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentReviews::route('/'),
            'edit' => Pages\EditPaymentReview::route('/{record}/edit'),
        ];
    }
}
