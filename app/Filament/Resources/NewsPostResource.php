<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsPostResource\Pages;
use App\Models\NewsPost;
use App\Support\UploadLimits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NewsPostResource extends Resource
{
    protected static ?string $model = NewsPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'ข่าวสาร';

    protected static ?string $navigationGroup = 'เว็บไซต์';

    protected static ?string $modelLabel = 'ข่าวสาร';

    protected static ?string $pluralModelLabel = 'ข่าวสาร';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('เนื้อหาข่าวสาร')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('หัวข้อข่าวสาร')
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
                                            ->helperText('ระบบจะสร้างจากหัวข้อให้อัตโนมัติ แต่สามารถแก้ไขเองได้'),

                                        Forms\Components\Textarea::make('excerpt')
                                            ->label('เนื้อหาข่าวแบบย่อ')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('content')
                                            ->label('เนื้อหาข่าวแบบละเอียด')
                                            ->required()
                                            ->fileAttachmentsDirectory('news-content')
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'h2',
                                                'h3',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'underline',
                                                'undo',
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['sm' => 3, 'lg' => 2]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('การเผยแพร่')
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('หมวดหมู่')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('user_id')
                                            ->label('ผู้เขียน')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(fn () => auth()->id())
                                            ->required(),

                                        Forms\Components\FileUpload::make('image_cover')
                                            ->label('รูปภาพหน้าปก')
                                            ->image()
                                            ->directory('news-covers')
                                            ->imageEditor()
                                            ->maxSize(UploadLimits::IMAGE_MAX_KB)
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('status')
                                            ->label('สถานะ')
                                            ->options([
                                                'draft' => 'ฉบับร่าง',
                                                'published' => 'เผยแพร่',
                                            ])
                                            ->default('draft')
                                            ->required()
                                            ->native(false),

                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('วันเวลาที่เผยแพร่')
                                            ->seconds(false)
                                            ->native(false),

                                        Forms\Components\Toggle::make('is_pinned')
                                            ->label('ปักหมุดข่าว')
                                            ->default(false),

                                        Forms\Components\TextInput::make('views_count')
                                            ->label('จำนวนเข้าชม')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                    ]),
                            ])
                            ->columnSpan(['sm' => 3, 'lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_cover')
                    ->label('รูป')
                    ->square()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('หัวข้อข่าวสาร')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('หมวดหมู่')
                    ->badge()
                    ->color(fn ($record): string => $record->category?->color ?: 'gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('ผู้เขียน')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_pinned')
                    ->label('ปักหมุด'),

                Tables\Columns\SelectColumn::make('status')
                    ->label('สถานะ')
                    ->options([
                        'draft' => 'ฉบับร่าง',
                        'published' => 'เผยแพร่',
                    ])
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('status_badge')
                    ->label('แสดงผล')
                    ->state(fn (NewsPost $record): string => $record->status)
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'เผยแพร่',
                        'draft' => 'ฉบับร่าง',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('เข้าชม')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('เผยแพร่เมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('หมวดหมู่')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'draft' => 'ฉบับร่าง',
                        'published' => 'เผยแพร่',
                    ]),

                Tables\Filters\TernaryFilter::make('is_pinned')
                    ->label('ปักหมุด'),
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
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsPosts::route('/'),
            'create' => Pages\CreateNewsPost::route('/create'),
            'edit' => Pages\EditNewsPost::route('/{record}/edit'),
        ];
    }
}
