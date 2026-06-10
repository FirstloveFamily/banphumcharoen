<?php

namespace App\Filament\Resources\DocumentMasterResource\RelationManagers;

use Filament\Forms;
use App\Support\UploadLimits;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class WorkerDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'workerDocuments';

    protected static ?string $recordTitleAttribute = 'attached_file_path';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('worker_id')
                ->label('แรงงาน')
                ->relationship('worker', 'first_name_th')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name_th)
                ->searchable(['first_name_th', 'last_name_th', 'passport_number'])
                ->required(),

            Forms\Components\DatePicker::make('expiry_date')
                ->label('วันหมดอายุ'),

            Forms\Components\FileUpload::make('attached_file_path')
                ->label('ไฟล์เอกสาร')
                ->helperText('เอกสารไม่เกิน 10 MB')
                ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                ->directory('worker-documents'),

            Forms\Components\Textarea::make('note')
                ->label('หมายเหตุ')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('worker.full_name_th')
                    ->label('แรงงาน')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('หมดอายุ')
                    ->date(),
                Tables\Columns\TextColumn::make('attached_file_path')
                    ->label('ไฟล์')
                    ->url(fn($state) => $state ? asset('storage/' . $state) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('expiring')->label('กำลังจะหมดอายุ')->query(fn($query) => $query->whereBetween('expiry_date', [now(), now()->addDays(30)])),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('เพิ่มเอกสาร'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ]);
    }
}
