<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'ผู้ใช้งาน';

    protected static ?string $modelLabel = 'ผู้ใช้งาน';

    protected static ?string $pluralModelLabel = 'ผู้ใช้งาน';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('ข้อมูลบัญชีผู้ใช้')
                    ->description('ข้อมูลพื้นฐานของผู้ใช้งานระบบ')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('ชื่อผู้ใช้งาน')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('กรอกชื่อจริง นามสกุล')
                                ->helperText('ชื่อเต็มของผู้ใช้งาน')
                                ->prefixIcon('heroicon-o-user')
                                ->unique(ignoreRecord: true),

                            Forms\Components\TextInput::make('email')
                                ->label('อีเมล')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->placeholder('example@domain.com')
                                ->helperText('อีเมลสำหรับเข้าสู่ระบบ')
                                ->prefixIcon('heroicon-o-envelope')
                                ->unique(ignoreRecord: true),
                        ]),

                        Grid::make(1)->schema([
                            Forms\Components\Select::make('roles')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->label('บทบาท')
                                ->required()
                                ->helperText('เลือกบทบาทของผู้ใช้งาน')
                                ->prefixIcon('heroicon-o-shield-check')
                                ->native(false),
                        ]),
                    ]),

                Section::make('ความปลอดภัย')
                    ->description('ข้อมูลรหัสผ่านและการเข้าสู่ระบบ')
                    ->icon('heroicon-o-lock-closed')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        Grid::make(1)->schema([
                            Forms\Components\TextInput::make('password')
                                ->label('รหัสผ่าน')
                                ->password()
                                ->maxLength(255)
                                ->placeholder(fn(string $context): string => $context === 'create' ? 'กรอกรหัสผ่าน' : 'กรอกรหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)')
                                ->helperText(fn(string $context): string => $context === 'create' ? 'กำหนดรหัสผ่านสำหรับผู้ใช้งานใหม่' : 'ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน')
                                ->prefixIcon('heroicon-o-key')
                                ->revealable()
                                ->required(fn(string $context): bool => $context === 'create')
                                ->dehydrated(fn($state) => filled($state))
                                ->same('password_confirmation'),
                        ]),

                        Grid::make(1)->schema([
                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('ยืนยันรหัสผ่าน')
                                ->password()
                                ->maxLength(255)
                                ->placeholder('กรอกรหัสผ่านอีกครั้ง')
                                ->helperText('ต้องตรงกับรหัสผ่านข้างบน')
                                ->prefixIcon('heroicon-o-key')
                                ->revealable()
                                ->required(fn(string $context): bool => $context === 'create')
                                ->dehydrated(false),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อผู้ใช้งาน')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->name)
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->email)
                    ->copyable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('บทบาท')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-shield-check')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'staff' => 'Staff',
                        'employer' => 'Employer',
                        default => $state ?? 'ไม่ระบุ',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'staff' => 'info',
                        'employer' => 'success',
                        default => 'gray',
                    })
                    ->icons([
                        'heroicon-o-shield-check' => fn($state) => $state === 'super_admin',
                        'heroicon-o-cog-6-tooth' => fn($state) => $state === 'admin',
                        'heroicon-o-user' => fn($state) => in_array($state, ['staff', 'employer']),
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดตล่าสุด')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->label('บทบาท')
                    ->placeholder('ทั้งหมด'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('ดู')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('แก้ไข')
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->label('ลบ')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('ลบที่เลือก'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistColumnSearchesInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('ข้อมูลบัญชี')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('ชื่อผู้ใช้งาน')
                            ->icon('heroicon-o-user')
                            ->weight('bold')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('email')
                            ->label('อีเมล')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->url(fn($state) => "mailto:{$state}"),

                        Infolists\Components\TextEntry::make('roles.name')
                            ->label('บทบาท')
                            ->icon('heroicon-o-shield-check')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'super_admin' => 'danger',
                                'admin' => 'warning',
                                'staff' => 'info',
                                'employer' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn($state) => match ($state) {
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin',
                                'staff' => 'Staff',
                                'employer' => 'Employer',
                                default => $state ?? 'ไม่ระบุ',
                            }),
                    ]),

                Infolists\Components\Section::make('ข้อมูลระบบ')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('สร้างเมื่อ')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('d/m/Y H:i:s'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('อัปเดตล่าสุด')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('d/m/Y H:i:s'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NotificationsRelationManager::class,
            RelationManagers\ActivityLogsRelationManager::class,
            RelationManagers\EmployersRelationManager::class,
            RelationManagers\AssignedJobOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            // 'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
