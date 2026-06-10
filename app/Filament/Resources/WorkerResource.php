<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerResource\Pages;
use App\Filament\Resources\WorkerResource\RelationManagers;
use App\Models\WorkerPrefix;
use App\Models\Worker;
use App\Support\UploadLimits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'แรงงาน';

    protected static ?string $modelLabel = 'แรงงาน';

    protected static ?string $pluralModelLabel = 'แรงงาน';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลทั่วไป')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('employer_id')
                                ->label('บริษัท')
                                ->relationship('employer', 'company_name')
                                ->searchable()
                                ->required(),

                            Forms\Components\Select::make('nationality_id')
                                ->label('สัญชาติ')
                                ->relationship('nationality', 'name_th')
                                ->searchable()
                                ->required(),

                            Forms\Components\Toggle::make('is_active')
                                ->label('สถานะ (ใช้งาน)')
                                ->helperText('เปิด = ใช้งาน')
                                ->default(true),
                        ]),

                    Forms\Components\FileUpload::make('photo_path')
                        ->label('รูปแรงงาน')
                        ->disk('public')
                        ->directory('worker-photos')
                        ->image()
                        ->imageEditor()
                        ->imagePreviewHeight('180')
                        ->maxSize(UploadLimits::IMAGE_MAX_KB)
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('worker_prefix_id')
                                ->label('คำนำหน้า')
                                ->options(fn () => WorkerPrefix::query()
                                    ->active()
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn (WorkerPrefix $prefix) => [
                                        $prefix->id => $prefix->name_th . ' / ' . $prefix->name_en,
                                    ]))
                                ->searchable()
                                ->placeholder('ไม่ระบุ'),

                            Forms\Components\TextInput::make('first_name_th')
                                ->label('ชื่อ (ไทย)')
                                ->required()
                                ->maxLength(150),

                            Forms\Components\TextInput::make('last_name_th')
                                ->label('นามสกุล (ไทย)')
                                ->maxLength(150),
                        ]),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('first_name_en')
                                ->label('ชื่อ (EN)')
                                ->required()
                                ->maxLength(150),

                            Forms\Components\TextInput::make('last_name_en')
                                ->label('นามสกุล (EN)')
                                ->maxLength(150),
                        ]),

                    Forms\Components\DatePicker::make('birth_date')
                        ->label('วันเกิด')
                        ->required(),
                ]),

            Forms\Components\Section::make('เอกสารสำคัญ')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('passport_number')
                                ->label('เลขหนังสือเดินทาง')
                                ->maxLength(100),

                            Forms\Components\DatePicker::make('passport_expiry')
                                ->label('วันหมดอายุ Passport')
                        ]),

                    Forms\Components\FileUpload::make('passport_file')
                        ->label('ไฟล์ Passport')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                        ->directory('workers/passports'),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('wp_number')
                                ->label('เลข Work Permit')
                                ->maxLength(100),

                            Forms\Components\DatePicker::make('wp_expiry')
                                ->label('วันหมดอายุ Work Permit'),
                        ]),

                    Forms\Components\FileUpload::make('wp_file')
                        ->label('ไฟล์ Work Permit')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                        ->directory('workers/wp'),
                ]),

            Forms\Components\Section::make('วีซ่า / รายงาน 90 วัน')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('visa_expiry')
                                ->label('วันหมดอายุวีซ่า'),

                            Forms\Components\DatePicker::make('report_90_days_due')
                                ->label('วันรายงาน 90 วัน'),
                        ]),

                    Forms\Components\FileUpload::make('visa_file')
                        ->label('ไฟล์ Visa')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                        ->directory('workers/visa'),

                    Forms\Components\FileUpload::make('report_90_days_file')
                        ->label('ไฟล์ 90 วัน')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                        ->directory('workers/report_90_days'),
                ]),

            Forms\Components\Section::make('ข้อมูลเสริม')
                ->schema([
                    Forms\Components\TextInput::make('gender')
                        ->label('เพศ')
                        ->maxLength(20),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable(),

            Tables\Columns\ImageColumn::make('photo_path')
                ->label('รูป')
                ->disk('public')
                ->circular()
                ->size(44),

            Tables\Columns\TextColumn::make('employer.company_name')
                ->label('บริษัท')
                ->searchable()
                ->limit(30),

            Tables\Columns\TextColumn::make('full_name_th')
                ->label('ชื่อ-สกุล (ไทย)')
                ->sortable()
                ->searchable()
                ->getStateUsing(fn($record) => $record->full_name_th),

            Tables\Columns\TextColumn::make('passport_number')
                ->label('Passport No.')
                ->searchable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('passport_expiry')
                ->label('Passport Expiry')
                ->badge()
                ->color(fn ($state): string => match (true) {
                    !$state => 'gray',
                    now()->copy()->parse($state)->isPast() => 'danger',
                    now()->copy()->parse($state)->diffInDays(now()) <= 30 => 'warning',
                    default => 'success',
                })
                ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-'),

            Tables\Columns\IconColumn::make('is_active')
                ->label('สถานะ')
                ->boolean(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('วันที่สร้าง')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            Tables\Filters\TernaryFilter::make('is_active')
                ->label('สถานะ'),

            Tables\Filters\Filter::make('expiring_passport')
                ->label('Passport กำลังจะหมดอายุ')
                ->query(fn(Builder $query) => $query->whereBetween('passport_expiry', [now(), now()->addDays(30)])),
        ])->actions([
            Tables\Actions\ViewAction::make()->label('ดู'),
            Tables\Actions\EditAction::make()->label('แก้ไข'),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label('ลบ'),
                Tables\Actions\RestoreBulkAction::make()->label('กู้คืน'),
            ]),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WorkerDocumentsRelationManager::class,
            RelationManagers\JobOrdersRelationManager::class,
            RelationManagers\AssignedJobOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkers::route('/'),
            'create' => Pages\CreateWorker::route('/create'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }
}
