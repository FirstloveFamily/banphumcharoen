<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NationalityResource\Pages;
use App\Filament\Resources\NationalityResource\RelationManagers;
use App\Models\Nationality;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NationalityResource extends Resource
{
    protected static ?string $model = Nationality::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';

    protected static ?string $navigationLabel = 'สัญชาติ';

    protected static ?string $navigationGroup = 'ตั้งค่าทั่วไป';

    protected static ?string $modelLabel = 'สัญชาติ';

    protected static ?string $pluralModelLabel = 'สัญชาติ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('ข้อมูลสัญชาติ')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_th')
                                            ->label('ชื่อสัญชาติ (ไทย)')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('name_en')
                                            ->label('ชื่อสัญชาติ (EN)')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('country_code')
                                            ->label('รหัสประเทศ')
                                            ->required()
                                            ->maxLength(10),
                                        Forms\Components\TextInput::make('icon_flag')
                                            ->label('ไอคอนธงชาติ')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['sm' => 3, 'md' => 2]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('ตั้งค่าระบบ')
                                    ->schema([
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
                Tables\Columns\TextColumn::make('name_th')
                    ->label('สัญชาติ (ไทย)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('สัญชาติ (EN)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->label('รหัสประเทศ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('icon_flag')
                    ->label('ไอคอน')
                    ->searchable(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNationalities::route('/'),
            'create' => Pages\CreateNationality::route('/create'),
            'edit' => Pages\EditNationality::route('/{record}/edit'),
        ];
    }
}
