<?php

namespace App\Filament\Resources\EmployerResource\RelationManagers;

use App\Filament\Resources\EmployerResource;
use App\Models\WorkerPrefix;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers';

    protected static ?string $recordTitleAttribute = 'full_name_th';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('worker_prefix_id')
                ->label('คำนำหน้า')
                ->options(fn () => WorkerPrefix::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->get()
                    ->mapWithKeys(fn (WorkerPrefix $prefix) => [
                        $prefix->id => $prefix->name_th . ' / ' . $prefix->name_en,
                    ]))
                ->searchable()
                ->placeholder('ไม่ระบุ'),

            Forms\Components\TextInput::make('first_name_th')
                ->label('ชื่อ (ไทย)')
                ->required()
                ->maxLength(150),

            Forms\Components\TextInput::make('last_name_th')
                ->label('นามสกุล (ไทย)')
                ->maxLength(150),

            Forms\Components\Select::make('nationality_id')
                ->label('สัญชาติ')
                ->relationship('nationality', 'name_th')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('passport_number')
                ->label('เลข Passport')
                ->maxLength(100),

            Forms\Components\DatePicker::make('passport_expiry')
                ->label('วันหมดอายุ Passport'),

            Forms\Components\Toggle::make('is_active')
                ->label('สถานะใช้งาน')
                ->default(true),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name_th')
                    ->label('ชื่อ-สกุล (ไทย)')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nationality.name_th')
                    ->label('สัญชาติ')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('passport_number')
                    ->label('Passport')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'ใช้งาน' : 'ไม่ใช้งาน')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('สถานะ'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('เพิ่มแรงงาน'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ]);
    }
}
