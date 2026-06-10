<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceChecklistResource\Pages;
use App\Models\ServiceChecklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceChecklistResource extends Resource
{
    protected static ?string $model = ServiceChecklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'เช็คลิสต์บริการ';

    protected static ?string $navigationGroup = 'การให้บริการ';

    protected static ?string $modelLabel = 'เช็คลิสต์บริการ';

    protected static ?string $pluralModelLabel = 'เช็คลิสต์บริการ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->label('บริการ')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('document_name')
                            ->label('รายการเอกสาร')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('ลำดับ')
                            ->numeric()
                            ->required()
                            ->default(1),

                        Forms\Components\Toggle::make('is_required')
                            ->label('จำเป็น')
                            ->helperText('เปิด = จำเป็นต้องใช้เอกสารนี้'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->label('บริการ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_name')
                    ->label('รายการเอกสาร')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ลำดับ')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label('จำเป็น')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('จำเป็น'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceChecklists::route('/'),
            'create' => Pages\CreateServiceChecklist::route('/create'),
            'edit' => Pages\EditServiceChecklist::route('/{record}/edit'),
        ];
    }
}
