<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobOrderLogResource\Pages;
use App\Models\JobOrderLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobOrderLogResource extends Resource
{
    protected static ?string $model = JobOrderLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'บันทึกกิจกรรมงาน';

    protected static ?string $navigationGroup = 'ระบบงาน';

    protected static ?string $modelLabel = 'บันทึกกิจกรรมงาน';

    protected static ?string $pluralModelLabel = 'บันทึกกิจกรรมงาน';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('job_order_id')
                            ->label('งาน')
                            ->relationship('jobOrder', 'job_number')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('ผู้ใช้งาน')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('action')
                            ->label('การกระทำ')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('รายละเอียด')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jobOrder.job_number')
                    ->label('งาน')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ผู้ใช้งาน')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('การกระทำ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('การกระทำ')
                    ->options(fn() => JobOrderLog::query()->distinct()->pluck('action', 'action')->toArray()),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobOrderLogs::route('/'),
            'create' => Pages\CreateJobOrderLog::route('/create'),
            'edit' => Pages\EditJobOrderLog::route('/{record}/edit'),
        ];
    }
}
