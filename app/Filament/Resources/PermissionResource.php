<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Filament\Resources\PermissionResource\RelationManagers;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PermissionResource extends Resource
{
    protected static ?string $model = \Spatie\Permission\Models\Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?string $navigationGroup = 'ระบบจัดการ';

    protected static ?string $navigationLabel = 'สิทธิ์ย่อย (Permissions)';
    
    protected static ?string $modelLabel = 'สิทธิ์ย่อย (Permission)';
    
    protected static ?string $pluralModelLabel = 'สิทธิ์ย่อย (Permissions)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ข้อมูลสิทธิ์ย่อย')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ชื่อสิทธิ์ย่อย')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อสิทธิ์ย่อย')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดตเมื่อ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
