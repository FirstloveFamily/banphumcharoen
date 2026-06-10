<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class EmployersRelationManager extends RelationManager
{
    protected static string $relationship = 'employers';

    protected static ?string $recordTitleAttribute = 'company_name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('role')
                ->label('บทบาทในบริษัท')
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
                Tables\Columns\TextColumn::make('company_name')
                    ->label('บริษัท')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_code')
                    ->label('รหัสบริษัท')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('ผู้ติดต่อ')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('โทรศัพท์')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('อีเมล')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('บทบาท')
                    ->searchable()
                    ->sortable(),
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
                    ->label('เชื่อมบริษัท')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('ยกเลิกการเชื่อมต่อ'),
                Tables\Actions\EditAction::make()->label('แก้ไขบทบาท'),
            ]);
    }
}
