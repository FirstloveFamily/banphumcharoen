<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class AssignedJobOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'assignedJobOrders';

    protected static ?string $recordTitleAttribute = 'job_number';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('job_number')
                ->label('เลขที่งาน')
                ->placeholder('ระบบจะสร้างรหัสงานอัตโนมัติ')
                ->disabled()
                ->dehydrated(fn ($state) => filled($state)),

            Forms\Components\Select::make('employer_id')
                ->label('บริษัท')
                ->relationship('employer', 'company_name')
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (\Filament\Forms\Set $set) => $set('worker_id', null)),

            Forms\Components\Select::make('worker_id')
                ->label('แรงงาน')
                ->relationship(
                    name: 'worker',
                    titleAttribute: 'first_name_th',
                    modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query, \Filament\Forms\Get $get) {
                        $employerId = $get('employer_id');
                        if ($employerId) {
                            return $query->where('employer_id', $employerId);
                        }
                        return $query->whereNull('id');
                    }
                )
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name_th)
                ->searchable(['first_name_th', 'last_name_th', 'passport_number'])
                ->disabled(fn (\Filament\Forms\Get $get): bool => ! $get('employer_id'))
                ->required(),

            Forms\Components\Select::make('service_id')
                ->label('บริการ')
                ->relationship('service', 'name')
                ->searchable()
                ->required(),

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
                    ->label('เลขที่งาน')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('employer.company_name')
                    ->label('บริษัท')
                    ->sortable(),
                Tables\Columns\TextColumn::make('worker.full_name_th')
                    ->label('แรงงาน')
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
