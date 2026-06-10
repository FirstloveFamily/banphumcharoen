<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerDocumentResource\Pages;
use App\Models\WorkerDocument;
use App\Support\UploadLimits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkerDocumentResource extends Resource
{
    protected static ?string $model = WorkerDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'เอกสารแรงงาน';

    protected static ?string $navigationGroup = 'เอกสาร';

    protected static ?string $modelLabel = 'เอกสารแรงงาน';

    protected static ?string $pluralModelLabel = 'เอกสารแรงงาน';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('worker_id')
                            ->label('แรงงาน')
                            ->relationship('worker', 'first_name_th')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name_th)
                            ->searchable(['first_name_th', 'last_name_th', 'passport_number'])
                            ->required(),

                        Forms\Components\Select::make('document_master_id')
                            ->label('ประเภทเอกสาร')
                            ->relationship('documentMaster', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('ไฟล์เอกสาร')
                            ->disk('public')
                            ->directory('worker-documents')
                            ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                            ->required(),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('วันหมดอายุ')
                            ->required(),

                        Forms\Components\Textarea::make('note')
                            ->label('หมายเหตุ')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('worker.full_name_th')
                    ->label('แรงงาน')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('documentMaster.name')
                    ->label('เอกสาร')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('วันหมดอายุ')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expired_status')
                    ->label('สถานะ')
                    ->badge()
                    ->state(fn($record) => $record->isExpired() ? 'หมดอายุ' : 'ปกติ')
                    ->color(fn (string $state): string => $state === 'หมดอายุ' ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('expired')
                    ->label('เอกสารหมดอายุ')
                    ->query(fn($query) => $query->whereDate('expiry_date', '<', now())),
                Tables\Filters\Filter::make('valid')
                    ->label('เอกสารยังไม่หมดอายุ')
                    ->query(fn($query) => $query->whereDate('expiry_date', '>=', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expiry_date', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkerDocuments::route('/'),
            'create' => Pages\CreateWorkerDocument::route('/create'),
            'edit' => Pages\EditWorkerDocument::route('/{record}/edit'),
        ];
    }
}
