<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentMasterResource\Pages;
use App\Filament\Resources\DocumentMasterResource\RelationManagers;
use App\Models\DocumentMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentMasterResource extends Resource
{
    protected static ?string $model = DocumentMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'ประเภทเอกสาร';

    protected static ?string $navigationGroup = 'ตั้งค่าทั่วไป';

    protected static ?string $modelLabel = 'ประเภทเอกสาร';

    protected static ?string $pluralModelLabel = 'ประเภทเอกสาร';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('รายละเอียดประเภทเอกสาร')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('ชื่อเอกสาร')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('code')
                                            ->label('รหัสเอกสาร')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\Textarea::make('description')
                                            ->label('คำอธิบาย')
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
                    ->label('ชื่อเอกสาร')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('รหัสเอกสาร')
                    ->searchable()
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WorkerDocumentsRelationManager::class,
            RelationManagers\JobOrderChecklistsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentMasters::route('/'),
            'create' => Pages\CreateDocumentMaster::route('/create'),
            'edit' => Pages\EditDocumentMaster::route('/{record}/edit'),
        ];
    }
}
