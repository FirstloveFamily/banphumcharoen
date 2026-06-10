<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployerResource\Pages;
use App\Filament\Resources\EmployerResource\RelationManagers;
use App\Models\Employer;
use App\Support\UploadLimits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployerResource extends Resource
{
    protected static ?string $model = Employer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'บริษัทจ้างแรงงาน';

    protected static ?string $modelLabel = 'บริษัทจ้างแรงงาน';

    protected static ?string $pluralModelLabel = 'บริษัทจ้างแรงงาน';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ข้อมูลบริษัท')
                    ->description('กรุณากรอกข้อมูลพื้นฐานของบริษัทจ้างแรงงาน')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('company_code')
                                    ->label('รหัสบริษัท')
                                    ->helperText('รหัสประจำตัวบริษัท (ระบบสร้างให้อัตโนมัติ)')
                                    ->required()
                                    ->unique('employers', 'company_code', ignoreRecord: true)
                                    ->maxLength(50)
                                    ->default(function () {
                                        $latest = \App\Models\Employer::latest('id')->first();
                                        $nextId = $latest ? $latest->id + 1 : 1;
                                        return 'EMP' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    })
                                    ->readOnly(),

                                Forms\Components\TextInput::make('company_name')
                                    ->label('ชื่อบริษัท')
                                    ->helperText('ชื่อเต็มของบริษัท')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('contact_name')
                                    ->label('ชื่อผู้ติดต่อ')
                                    ->helperText('ชื่อผู้ติดต่อประจำในบริษัท')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('tax_id')
                                    ->label('เลขที่ประเมิณภาษี')
                                    ->helperText('เลขประจำตัวผู้เสียภาษีอากร')
                                    ->maxLength(20),
                            ]),
                    ]),

                Forms\Components\Section::make('ข้อมูลติดต่อ')
                    ->description('หมายเลขติดต่อและช่องทางการสื่อสาร')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('เบอร์โทรศัพท์')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('email')
                                    ->label('อีเมล')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Textarea::make('address')
                            ->label('ที่อยู่')
                            ->helperText('ที่อยู่เต็มประจำสำนักงาน')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('ข้อมูลเพิ่มเติม')
                    ->description('ข้อมูลรูปภาพและหมายเหตุ')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('โลโก้บริษัท')
                            ->helperText('อัปโหลดโลโก้ของบริษัท (jpg, png)')
                            ->image()
                            ->maxSize(UploadLimits::IMAGE_MAX_KB)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('หมายเหตุ')
                            ->helperText('หมายเหตุเพิ่มเติมเกี่ยวกับบริษัท')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('สถานะ (ใช้งาน)')
                            ->helperText('เปิด = ใช้งาน, ปิด = ไม่ใช้งาน')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('โลโก้')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('company_code')
                    ->label('รหัสบริษัท')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('ชื่อบริษัท')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('ผู้ติดต่อ')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('โทรศัพท์')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('สถานะ')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('สถานะการใช้งาน')
                    ->placeholder('ทั้งหมด')
                    ->trueLabel('ใช้งาน')
                    ->falseLabel('ไม่ใช้งาน'),

                Tables\Filters\TrashedFilter::make()
                    ->label('รายการที่ลบ'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('ดู'),

                Tables\Actions\EditAction::make()
                    ->label('แก้ไข'),

                Tables\Actions\ForceDeleteAction::make()
                    ->label('ลบ'),

                Tables\Actions\RestoreAction::make()
                    ->label('กู้คืน'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('ลบรายการที่เลือก'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('กู้คืนรายการที่เลือก'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('ลบถาวรรายการที่เลือก'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\WorkersRelationManager::class,
            RelationManagers\JobOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployers::route('/'),
            'create' => Pages\CreateEmployer::route('/create'),
            'edit' => Pages\EditEmployer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
