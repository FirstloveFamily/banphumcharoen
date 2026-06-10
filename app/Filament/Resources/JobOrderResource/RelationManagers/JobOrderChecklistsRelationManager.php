<?php

namespace App\Filament\Resources\JobOrderResource\RelationManagers;

use Filament\Forms;
use App\Support\UploadLimits;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class JobOrderChecklistsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklists';

    protected static ?string $recordTitleAttribute = 'document_master_id';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('document_master_id')
                ->label('เอกสาร')
                ->relationship('documentMaster', 'name')
                ->searchable()
                ->required(),

            Forms\Components\Toggle::make('is_required')
                ->label('ต้องการเอกสาร')
                ->default(true),

            Forms\Components\Select::make('status')
                ->label('สถานะ')
                ->options([
                    'pending' => 'รอดำเนินการ',
                    'received' => 'ได้รับแล้ว',
                    'verified' => 'ตรวจสอบแล้ว',
                    'rejected' => 'ปฏิเสธ',
                    'missing' => 'ขาดเอกสาร',
                ])
                ->default('pending'),

            Forms\Components\DatePicker::make('received_at')
                ->label('วันได้รับเอกสาร'),

            Forms\Components\FileUpload::make('attached_file_path')
                ->label('ไฟล์แนบ')
                ->helperText('ขนาดรูปไม่เกิน 3 MB, เอกสารไม่เกิน 10 MB')
                ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                ->directory('job-order-checklists'),

            Forms\Components\Select::make('verified_by')
                ->label('ตรวจโดย')
                ->relationship('verifiedBy', 'name')
                ->searchable(),

            Forms\Components\DatePicker::make('verified_at')
                ->label('วันที่ตรวจสอบ'),

            Forms\Components\Textarea::make('remark')
                ->label('หมายเหตุ')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documentMaster.name')
                    ->label('เอกสาร')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอดำเนินการ',
                        'received' => 'ได้รับแล้ว',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ปฏิเสธ',
                        'missing' => 'ขาดเอกสาร',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'received' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'missing' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),
                Tables\Columns\TextColumn::make('received_at')
                    ->label('วันที่ได้รับ')
                    ->date(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('วันที่ตรวจสอบ')
                    ->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'received' => 'ได้รับแล้ว',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ปฏิเสธ',
                        'missing' => 'ขาดเอกสาร',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('เพิ่มเช็คลิสต์'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ]);
    }
}
