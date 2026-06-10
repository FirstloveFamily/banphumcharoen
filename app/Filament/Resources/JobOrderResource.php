<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobOrderResource\Pages;
use App\Filament\Resources\JobOrderResource\RelationManagers;
use App\Models\JobOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobOrderResource extends Resource
{
    protected static ?string $model = JobOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'ใบสั่งงาน';

    protected static ?string $navigationGroup = 'ระบบงาน';

    protected static ?string $modelLabel = 'ใบสั่งงาน';

    protected static ?string $pluralModelLabel = 'ใบสั่งงาน';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('รายละเอียดใบสั่งงาน')
                                    ->schema([
                                        Forms\Components\TextInput::make('job_number')
                                            ->label('รหัสงาน')
                                            ->placeholder('ระบบจะสร้างรหัสงานอัตโนมัติ')
                                            ->disabled()
                                            ->dehydrated(fn ($state) => filled($state)),
                                        Forms\Components\Select::make('employer_id')
                                            ->label('นายจ้าง/บริษัท')
                                            ->relationship('employer', 'company_name')
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (Forms\Set $set) => $set('worker_id', null)),
                                        Forms\Components\Select::make('worker_id')
                                            ->label('แรงงาน')
                                            ->relationship(
                                                name: 'worker',
                                                titleAttribute: 'first_name_th',
                                                modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query, Forms\Get $get) {
                                                    $employerId = $get('employer_id');
                                                    if ($employerId) {
                                                        return $query->where('employer_id', $employerId);
                                                    }
                                                    return $query->whereNull('id');
                                                }
                                            )
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name_th)
                                            ->searchable(['first_name_th', 'last_name_th', 'passport_number'])
                                            ->disabled(fn (Forms\Get $get): bool => ! $get('employer_id'))
                                            ->required(),
                                        Forms\Components\Select::make('service_id')
                                            ->label('บริการ')
                                            ->relationship('service', 'name')
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\Select::make('assigned_user_id')
                                            ->label('เจ้าหน้าที่รับผิดชอบ')
                                            ->relationship('assignedUser', 'name')
                                            ->searchable(),
                                        Forms\Components\Textarea::make('notes')
                                            ->label('หมายเหตุ')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['sm' => 3, 'md' => 2]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('สถานะและการชำระเงิน')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('สถานะงาน')
                                            ->required()
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
                                        Forms\Components\Select::make('payment_status')
                                            ->label('สถานะการชำระเงิน')
                                            ->required()
                                            ->options([
                                                'pending' => 'รอดำเนินการ',
                                                'partial' => 'ชำระบางส่วน',
                                                'paid' => 'ชำระแล้ว',
                                                'cancelled' => 'ยกเลิก',
                                            ])
                                            ->default('pending'),
                                        Forms\Components\TextInput::make('service_fee')
                                            ->label('ค่าบริการรวม')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        Forms\Components\TextInput::make('paid_amount')
                                            ->label('ชำระแล้ว')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->helperText('ระบบรวมจากรายการชำระเงินที่ตรวจสอบแล้ว'),
                                        Forms\Components\Placeholder::make('remaining_amount')
                                            ->label('ยอดคงเหลือ')
                                            ->content(fn (?JobOrder $record): string => $record
                                                ? number_format($record->getRemainingAmount(), 2) . ' บาท'
                                                : '0.00 บาท'),
                                    ]),
                                    
                                Forms\Components\Section::make('ข้อมูลเวลาและความสำคัญ')
                                    ->schema([
                                        Forms\Components\Select::make('priority')
                                            ->label('ความสำคัญ')
                                            ->required()
                                            ->options([
                                                'low' => 'ต่ำ',
                                                'medium' => 'ปานกลาง',
                                                'high' => 'สูง',
                                                'urgent' => 'ด่วน',
                                            ])
                                            ->default('medium'),
                                        Forms\Components\DatePicker::make('due_date')
                                            ->label('วันที่กำหนดเสร็จ'),
                                        Forms\Components\DateTimePicker::make('started_at')
                                            ->label('เวลาเริ่มงาน'),
                                        Forms\Components\DateTimePicker::make('completed_at')
                                            ->label('เวลาเสร็จสิ้น'),
                                    ]),
                            ])
                            ->columnSpan(['sm' => 3, 'md' => 1]),
                    ]),
            ]);
    }

   public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('job_number')
                ->label('หมายเลขงาน')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('employer.company_name')
                ->label('บริษัท')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('worker.full_name_th')
                ->label('แรงงาน')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('service.name')
                ->label('บริการ')
                ->sortable(),
            Tables\Columns\TextColumn::make('assignedUser.name')
                ->label('เจ้าหน้าที่รับผิดชอบ')
                ->sortable(),
                
            // Updated payment_status badge
            Tables\Columns\TextColumn::make('payment_status')
                ->label('สถานะชำระเงิน')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'รอดำเนินการ',
                    'partial' => 'ชำระบางส่วน',
                    'paid' => 'ชำระแล้ว',
                    'cancelled' => 'ยกเลิก',
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'partial' => 'primary',
                    'paid' => 'success',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),

            // Updated status badge
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
                    'pending' => 'warning',
                    'processing' => 'primary',
                    'waiting_document' => 'secondary',
                    'completed' => 'success',
                    'approved' => 'info',
                    'cancelled' => 'danger',
                    'rejected' => 'gray',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('priority')
                ->label('ความสำคัญ')
                ->sortable(),
            Tables\Columns\TextColumn::make('service_fee')
                ->label('ค่าบริการ')
                ->money('thb', true)
                ->sortable(),
            Tables\Columns\TextColumn::make('paid_amount')
                ->label('ชำระแล้ว')
                ->money('thb', true)
                ->sortable(),
            Tables\Columns\TextColumn::make('remaining_amount')
                ->label('คงเหลือ')
                ->state(fn (JobOrder $record): float => $record->getRemainingAmount())
                ->money('thb', true)
                ->sortable(query: fn ($query, string $direction) => $query->orderByRaw('(service_fee - paid_amount) ' . $direction)),
            Tables\Columns\TextColumn::make('due_date')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('started_at')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('completed_at')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('deleted_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            RelationManagers\JobOrderChecklistsRelationManager::class,
            RelationManagers\JobOrderPaymentsRelationManager::class,
            RelationManagers\JobOrderLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobOrders::route('/'),
            'create' => Pages\CreateJobOrder::route('/create'),
            'edit' => Pages\EditJobOrder::route('/{record}/edit'),
        ];
    }
}
