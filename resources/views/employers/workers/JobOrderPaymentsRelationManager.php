<?php

namespace App\Filament\Resources\JobOrderResource\RelationManagers;

use App\Models\JobOrderLog;
use App\Models\JobOrderPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JobOrderPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $modelLabel = 'การชำระเงิน';

    protected static ?string $pluralModelLabel = 'ประวัติชำระเงิน';

    protected static ?string $title = 'ประวัติชำระเงิน';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // We will use Actions to create/edit payments for better control
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('ยอดชำระ')
                    ->money('thb')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'รอตรวจสอบ',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ไม่ผ่าน',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('วันที่ชำระ')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('ช่องทาง')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'เงินสด',
                        'transfer' => 'โอนเงิน',
                        'promptpay' => 'พร้อมเพย์',
                        'credit_card' => 'บัตรเครดิต',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('เลขอ้างอิง')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('note')
                    ->label('หมายเหตุ')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('ผู้แจ้งชำระ')
                    ->default('ลูกค้า')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('view_slip')
                    ->label('ดูสลิป')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->url(fn(JobOrderPayment $record) => asset('storage/' . $record->slip_path), shouldOpenInNewTab: true)
                    ->visible(fn(JobOrderPayment $record) => !empty($record->slip_path)),

                Action::make('verify_payment')
                    ->label('ยืนยันสลิป')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('ยืนยันการชำระเงิน')
                    ->modalDescription('คุณต้องการยืนยันการชำระเงินนี้ใช่หรือไม่? ระบบจะอัปเดตยอดคงเหลือของใบงานนี้')
                    ->action(function (JobOrderPayment $record) {
                        DB::transaction(function () use ($record) {
                            $record->update(['status' => 'verified']);

                            // Update Job Order status
                            $jobOrder = $this->getOwnerRecord();
                            $jobOrder->updatePaymentStatus();

                            // Create Log
                            JobOrderLog::create([
                                'job_order_id' => $jobOrder->id,
                                'user_id' => auth()->id(),
                                'action' => 'ยืนยันการชำระเงิน',
                                'description' => 'ยืนยันยอด ' . number_format($record->amount, 2) . ' บาท',
                            ]);
                        });

                        Notification::make()
                            ->title('ยืนยันการชำระเงินสำเร็จ')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(JobOrderPayment $record): bool => $record->status === 'pending'),

                Action::make('reject_payment')
                    ->label('ปฏิเสธสลิป')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('เหตุผลที่ปฏิเสธ')
                            ->required()
                            ->placeholder('เช่น สลิปไม่ถูกต้อง, ยอดเงินไม่ตรง'),
                    ])
                    ->action(function (JobOrderPayment $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'note' => ($record->note ? $record->note . "\n" : '') . 'เหตุผลที่ปฏิเสธ: ' . $data['rejection_reason'],
                        ]);

                        // Create Log
                        JobOrderLog::create([
                            'job_order_id' => $this->getOwnerRecord()->id,
                            'user_id' => auth()->id(),
                            'action' => 'ปฏิเสธการชำระเงิน',
                            'description' => 'ปฏิเสธยอด ' . number_format($record->amount, 2) . ' บาท เนื่องจาก: ' . $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('ปฏิเสธการชำระเงินเรียบร้อย')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn(JobOrderPayment $record): bool => $record->status === 'pending'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
