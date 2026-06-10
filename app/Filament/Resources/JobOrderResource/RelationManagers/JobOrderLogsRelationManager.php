<?php

namespace App\Filament\Resources\JobOrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class JobOrderLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $recordTitleAttribute = 'action';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->label('การกระทำ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('โดย'),
                Tables\Columns\TextColumn::make('description')
                    ->label('คำอธิบาย')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
