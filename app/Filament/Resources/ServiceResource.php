<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'บริการ';

    protected static ?string $navigationGroup = 'ระบบงาน';

    protected static ?string $modelLabel = 'บริการ';

    protected static ?string $pluralModelLabel = 'บริการ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('รายละเอียดบริการ')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('ชื่อบริการ')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('code')
                                            ->label('รหัสบริการ')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\Textarea::make('description')
                                            ->label('รายละเอียด')
                                            ->columnSpanFull()
                                            ->rows(4),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['sm' => 3, 'md' => 2]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('ตั้งค่าระบบ')
                                    ->schema([
                                        Forms\Components\TextInput::make('alert_days_before_expiry')
                                            ->label('แจ้งเตือนล่วงหน้า (วัน)')
                                            ->helperText('จำนวนวันเพื่อแจ้งเตือนก่อนเอกสาร/วีซ่าหมดอายุ')
                                            ->required()
                                            ->numeric()
                                            ->default(30),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('สถานะการใช้งาน')
                                            ->default(true)
                                            ->required(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อบริการ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('รหัสบริการ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alert_days_before_expiry')
                    ->label('แจ้งเตือน (วัน)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('สถานะ')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดตเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('สถานะการใช้งาน'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('ลบ'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ServiceChecklistsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
