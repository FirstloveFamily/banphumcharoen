<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsCategoryResource\Pages;
use App\Models\NewsCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'หมวดหมู่ข่าวสาร';

    protected static ?string $navigationGroup = 'เว็บไซต์';

    protected static ?string $modelLabel = 'หมวดหมู่ข่าวสาร';

    protected static ?string $pluralModelLabel = 'หมวดหมู่ข่าวสาร';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('รายละเอียดหมวดหมู่')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ชื่อหมวดหมู่')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                $set('slug', Str::slug($state ?? ''));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL SEO')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('ระบบจะสร้างจากชื่อหมวดหมู่ให้อัตโนมัติ แต่สามารถแก้ไขเองได้'),

                        Forms\Components\Select::make('color')
                            ->label('สีหมวดหมู่')
                            ->options([
                                'gray' => 'เทา',
                                'primary' => 'หลัก',
                                'info' => 'ฟ้า',
                                'success' => 'เขียว',
                                'warning' => 'เหลือง',
                                'danger' => 'แดง',
                            ])
                            ->default('primary')
                            ->native(false)
                            ->helperText('ใช้แยกสีหมวดหมู่บนตารางข่าวสาร'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อหมวดหมู่')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL SEO')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('color')
                    ->label('สี')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ?: 'ไม่ระบุ')
                    ->color(fn (?string $state): string => $state ?: 'gray'),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label('จำนวนข่าว')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดตเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('posts');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsCategories::route('/'),
            'create' => Pages\CreateNewsCategory::route('/create'),
            'edit' => Pages\EditNewsCategory::route('/{record}/edit'),
        ];
    }
}
