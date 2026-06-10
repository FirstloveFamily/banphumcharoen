<?php

namespace App\Filament\Resources\JobOrderResource\RelationManagers;

use Filament\Forms;
use App\Support\UploadLimits;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class JobOrderPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'payment_reference';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')
                ->label('จำนวนเงิน')
                ->numeric()
                ->required(),

            Forms\Components\DatePicker::make('payment_date')
                ->label('วันที่ชำระเงิน')
                ->required(),

            Forms\Components\Select::make('payment_method')
                ->label('ช่องทางชำระเงิน')
                ->options([
                    'cash' => 'เงินสด',
                    'transfer' => 'โอนเงิน',
                    'promptpay' => 'พร้อมเพย์',
                    'credit_card' => 'บัตรเครดิต',
                ])
                ->required(),

            Forms\Components\TextInput::make('payment_reference')
                ->label('อ้างอิงการชำระเงิน')
                ->maxLength(255),

            Forms\Components\FileUpload::make('slip_path')
                ->label('สลิปการโอน')
                ->helperText('ขนาดรูปไม่เกิน 3 MB, เอกสารไม่เกิน 10 MB')
                ->disk('public')
                ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                ->directory('job-order-payments'),

            Forms\Components\Select::make('status')
                ->label('สถานะ')
                ->options([
                    'pending' => 'รอดำเนินการ',
                    'verified' => 'ตรวจสอบแล้ว',
                    'rejected' => 'ปฏิเสธ',
                ])
                ->default('pending'),

            Forms\Components\Textarea::make('note')
                ->label('หมายเหตุ')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('จำนวนเงิน')
                    ->money('thb', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('วิธีชำระเงิน')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('วันที่ชำระ')
                    ->date()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('slip_path')
                    ->label('สลิป')
                    ->disk('public')
                    ->square()
                    ->size(56)
                    ->url(fn (?string $state): ?string => $state ? asset('storage/' . $state) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ปฏิเสธ',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('บันทึกชำระเงิน')
                    ->after(fn () => $this->getOwnerRecord()->refresh()->syncPaymentSummary()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('แก้ไข')
                    ->after(fn () => $this->getOwnerRecord()->refresh()->syncPaymentSummary()),
                Tables\Actions\DeleteAction::make()
                    ->label('ลบ')
                    ->after(fn () => $this->getOwnerRecord()->refresh()->syncPaymentSummary()),
            ]);
    }
}
