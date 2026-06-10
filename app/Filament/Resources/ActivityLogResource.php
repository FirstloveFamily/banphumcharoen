<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'บันทึกกิจกรรม';

    protected static ?string $navigationGroup = 'ระบบงาน';

    protected static ?string $modelLabel = 'บันทึกกิจกรรม';

    protected static ?string $pluralModelLabel = 'บันทึกกิจกรรม';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('รายละเอียดกิจกรรม')
                                    ->schema([
                                        Forms\Components\TextInput::make('action')
                                            ->label('การกระทำ (Action)')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('model_type')
                                            ->label('ประเภทข้อมูล (Model Type)')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('model_id')
                                            ->label('รหัสข้อมูล (Model ID)')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\Textarea::make('description')
                                            ->label('คำอธิบาย')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['sm' => 3, 'md' => 2]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('ผู้ดำเนินการ')
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('ผู้ใช้งาน')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->required(),
                                    ]),
                            ])
                            ->columnSpan(['sm' => 3, 'md' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ผู้ใช้งาน')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Model Type')
                    ->searchable()
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
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('ผู้ใช้งาน')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('recent')
                    ->label('ย้อนหลัง 30 วัน')
                    ->query(fn($query) => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('ลบ'),
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
            'index' => Pages\ListActivityLogs::route('/'),
            'create' => Pages\CreateActivityLog::route('/create'),
            'edit' => Pages\EditActivityLog::route('/{record}/edit'),
        ];
    }
}
