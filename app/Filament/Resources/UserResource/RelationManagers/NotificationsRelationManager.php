<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class NotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'notifications';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('หัวข้อ')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('message')
                ->label('ข้อความ')
                ->required()
                ->rows(4)
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_read')
                ->label('อ่านแล้ว')
                ->default(false),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('หัวข้อ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('ข้อความ')
                    ->limit(50),
                
                // Fixed Filament v3 Badge formatting here
                Tables\Columns\TextColumn::make('is_read')
                    ->label('สถานะอ่าน')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'อ่านแล้ว' : 'ยังไม่อ่าน')
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('สถานะอ่าน'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('สร้างแจ้งเตือน'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ]);
    }
}