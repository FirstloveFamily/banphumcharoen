<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class ServiceChecklistsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklists';

    protected static ?string $recordTitleAttribute = 'document_name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('document_name')
                ->label('ชื่อเอกสาร')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('sort_order')
                ->label('ลำดับ')
                ->numeric()
                ->default(1),

            Forms\Components\Toggle::make('is_required')
                ->label('จำเป็นต้องใช้')
                ->default(true),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_name')
                    ->label('ชื่อเอกสาร')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ลำดับ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_required')
                    ->label('จำเป็น')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'ใช่' : 'ไม่ใช่')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('เพิ่มเช็คลิสต์'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ]);
    }
}
