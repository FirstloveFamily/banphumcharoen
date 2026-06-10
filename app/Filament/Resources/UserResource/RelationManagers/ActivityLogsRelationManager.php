<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';

    protected static ?string $recordTitleAttribute = 'action';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('action')
                ->label('Action')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('model_type')
                ->label('Model Type')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('model_id')
                ->label('Model ID')
                ->required()
                ->maxLength(100),

            Forms\Components\Textarea::make('description')
                ->label('คำอธิบาย')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Model Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('model_id')
                    ->label('Model ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('คำอธิบาย')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('สร้างบันทึก'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ]);
    }
}
