<?php

namespace App\Filament\Resources\DocumentMasterResource\RelationManagers;

use Filament\Forms;
use App\Support\UploadLimits;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class JobOrderChecklistsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobOrderChecklists';

    protected static ?string $recordTitleAttribute = 'job_order_id';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('job_order_id')
                ->label('รหัสงาน')
                ->relationship('jobOrder', 'job_number')
                ->searchable()
                ->required(),

            Forms\Components\Toggle::make('is_required')
                ->label('จำเป็น')
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
                ->label('วันที่ได้รับ'),

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
                Tables\Columns\TextColumn::make('jobOrder.job_number')
                    ->label('รหัสงาน')
                    ->sortable()
                    ->searchable(),
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
                    ->label('จำเป็น')
                    ->boolean(),
                Tables\Columns\TextColumn::make('verifiedBy.name')
                    ->label('ตรวจโดย'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
