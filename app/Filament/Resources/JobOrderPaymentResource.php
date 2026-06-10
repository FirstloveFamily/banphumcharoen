<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobOrderPaymentResource\Pages;
use App\Models\JobOrderPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobOrderPaymentResource extends Resource
{
    protected static ?string $model = JobOrderPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-bangladeshi';

    protected static ?string $navigationLabel = 'ชำระเงินงาน';

    protected static ?string $navigationGroup = 'ระบบงาน';

    protected static ?string $modelLabel = 'การชำระเงิน';

    protected static ?string $pluralModelLabel = 'การชำระเงิน';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('รายละเอียดการชำระเงิน')
                                    ->schema([
                                        Forms\Components\Select::make('job_order_id')
                                            ->label('งาน')
                                            ->relationship('jobOrder', 'job_number')
                                            ->searchable()
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('amount')
                                            ->label('จำนวนเงิน')
                                            ->numeric()
                                            ->required()
                                            ->prefix('฿'),

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
                                            ->label('อ้างอิงการชำระ')
                                            ->maxLength(255)
                                            ->nullable(),

                                        Forms\Components\DatePicker::make('payment_date')
                                            ->label('วันที่ชำระ')
                                            ->required(),
                                            
                                        Forms\Components\Textarea::make('note')
                                            ->label('หมายเหตุ')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('หลักฐานการชำระ')
                                    ->schema([
                                        Forms\Components\FileUpload::make('slip_path')
                                            ->label('สลิป / หลักฐานการโอน')
                                            ->disk('public')
                                            ->directory('job-order-payments')
                                            ->image()
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(['sm' => 3, 'md' => 2]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('สถานะและการรับเงิน')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('สถานะ')
                                            ->options([
                                                'pending' => 'รอดำเนินการ',
                                                'verified' => 'ตรวจสอบแล้ว',
                                                'rejected' => 'ถูกปฏิเสธ',
                                            ])
                                            ->required()
                                            ->default('pending'),

                                        Forms\Components\Select::make('received_by')
                                            ->label('รับโดย')
                                            ->relationship('receiver', 'name')
                                            ->searchable()
                                            ->nullable(),
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
                Tables\Columns\TextColumn::make('jobOrder.job_number')
                    ->label('งาน')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('จำนวนเงิน')
                    ->money('THB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('ช่องทาง')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('slip_path')
                    ->label('สลิป')
                    ->disk('public')
                    ->square()
                    ->size(56)
                    ->url(fn (?string $state): ?string => $state ? asset('storage/' . $state) : null)
                    ->openUrlInNewTab(),

                // Fixed: Upgraded deprecated BadgeColumn to Filament v3 TextColumn badge
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอดำเนินการ',
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

                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('รับโดย')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('วันที่ชำระ')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn (JobOrderPayment $record) => $record->jobOrder?->syncPaymentSummary()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn (JobOrderPayment $record) => $record->jobOrder?->syncPaymentSummary()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobOrderPayments::route('/'),
            'create' => Pages\CreateJobOrderPayment::route('/create'),
            'edit' => Pages\EditJobOrderPayment::route('/{record}/edit'),
        ];
    }
}
