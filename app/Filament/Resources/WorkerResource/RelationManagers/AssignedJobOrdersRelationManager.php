<?php

namespace App\Filament\Resources\WorkerResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class AssignedJobOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'jobOrders';

    protected static ?string $title = 'งานที่มอบหมาย';
    protected static ?string $recordTitleAttribute = 'job_number';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('job_number')
                ->label('รหัสงาน')
                ->required()
                ->maxLength(100),

            Forms\Components\Select::make('service_id')
                ->label('บริการ')
                ->relationship('service', 'name')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('assigned_user_id')
                ->label('ผู้รับผิดชอบ')
                ->relationship('assignedUser', 'name')
                ->searchable(),

            Forms\Components\TextInput::make('service_fee')
                ->label('ค่าบริการ')
                ->numeric()
                ->required()
                ->default(0),

            Forms\Components\TextInput::make('paid_amount')
                ->label('ยอดชำระแล้ว')
                ->numeric()
                ->required()
                ->default(0),

            Forms\Components\Select::make('payment_status')
                ->label('สถานะการชำระเงิน')
                ->options([
                    'pending' => 'รอดำเนินการ',
                    'partial' => 'ชำระบางส่วน',
                    'paid' => 'ชำระแล้ว',
                    'cancelled' => 'ยกเลิก',
                ])
                ->default('pending'),

            Forms\Components\Select::make('status')
                ->label('สถานะงาน')
                ->options([
                    'pending' => 'รอดำเนินการ',
                    'processing' => 'กำลังดำเนินการ',
                    'waiting_document' => 'รอเอกสาร',
                    'approved' => 'อนุมัติ',
                    'completed' => 'เสร็จสิ้น',
                    'cancelled' => 'ยกเลิก',
                    'rejected' => 'ปฏิเสธ',
                ])
                ->default('pending'),

            Forms\Components\Select::make('priority')
                ->label('ความเร่งด่วน')
                ->options([
                    'low' => 'ต่ำ',
                    'medium' => 'ปานกลาง',
                    'high' => 'สูง',
                    'urgent' => 'ด่วน',
                ])
                ->default('medium'),

            Forms\Components\DatePicker::make('due_date')
                ->label('วันที่ครบกำหนด'),

            Forms\Components\Textarea::make('notes')
                ->label('หมายเหตุ')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_number')
                    ->label('รหัสงาน')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('บริการ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('ผู้รับผิดชอบ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะงาน')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอดำเนินการ',
                        'processing' => 'กำลังดำเนินการ',
                        'waiting_document' => 'รอเอกสาร',
                        'approved' => 'อนุมัติ',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                        'rejected' => 'ปฏิเสธ',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'waiting_document' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('ครบกำหนด')
                    ->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะงาน')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'processing' => 'กำลังดำเนินการ',
                        'waiting_document' => 'รอเอกสาร',
                        'approved' => 'อนุมัติ',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                        'rejected' => 'ปฏิเสธ',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('สถานะการชำระเงิน')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'partial' => 'ชำระบางส่วน',
                        'paid' => 'ชำระแล้ว',
                        'cancelled' => 'ยกเลิก',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('สร้างงานใหม่'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ]);
    }
}
