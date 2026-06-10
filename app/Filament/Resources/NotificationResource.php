<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'การแจ้งเตือน';

    protected static ?string $pluralModelLabel = 'การแจ้งเตือน';

    public static function getNavigationBadge(): ?string
    {
        return (string) Notification::query()->where('is_read', false)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('ผู้ใช้งาน')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

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
                    ->helperText('ปิด = ยังไม่อ่าน, เปิด = อ่านแล้ว')
                    ->default(false),
            ]);
    }

  public static function table(Table $table): Table
{
    return $table
        ->columns([
            // Updated to the v3 TextColumn badge format
            Tables\Columns\TextColumn::make('is_read')
                ->label('สถานะ')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'อ่านแล้ว' : 'ยังไม่อ่าน')
                ->color(fn (bool $state): string => $state ? 'success' : 'warning'),

            Tables\Columns\TextColumn::make('title')
                ->label('หัวข้อ')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
                ->label('ผู้ใช้งาน')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('วันที่สร้าง')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('is_read')
                ->label('สถานะการอ่าน'),
            Tables\Filters\Filter::make('unread')
                ->label('ยังไม่อ่าน')
                ->query(fn($query) => $query->where('is_read', false)),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
