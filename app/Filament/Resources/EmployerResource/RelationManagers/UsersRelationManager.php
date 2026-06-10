<?php

namespace App\Filament\Resources\EmployerResource\RelationManagers;

use App\Filament\Resources\EmployerResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('role')
                ->label('บทบาท')
                ->options([
                    'owner' => 'เจ้าของ',
                    'hr' => 'HR',
                    'accounting' => 'บัญชี',
                    'viewer' => 'ผู้ชม',
                ])
                ->required(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อผู้ใช้งาน')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('อีเมล')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('บทบาท')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pivot.role')
                    ->label('บทบาท')
                    ->options([
                        'owner' => 'เจ้าของ',
                        'hr' => 'HR',
                        'accounting' => 'บัญชี',
                        'viewer' => 'ผู้ชม',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('เชื่อมผู้ใช้')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('ยกเลิกการเชื่อมต่อ'),
                Tables\Actions\EditAction::make()->label('แก้ไขบทบาท'),
            ]);
    }
}
