<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobOrderChecklistResource\Pages;
use App\Models\JobOrderLog;
use App\Models\JobOrderChecklist;
use App\Support\UploadLimits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JobOrderChecklistResource extends Resource
{
    protected static ?string $model = JobOrderChecklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'รายการเช็คลิสต์งาน';

    protected static ?string $navigationGroup = 'ระบบงาน';

    protected static ?string $modelLabel = 'รายการเช็คลิสต์งาน';

    protected static ?string $pluralModelLabel = 'รายการเช็คลิสต์งาน';

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

                        Forms\Components\Select::make('document_master_id')
                            ->label('เอกสาร')
                            ->relationship('documentMaster', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Toggle::make('is_required')
                            ->label('จำเป็น')
                            ->helperText('เปิด = ต้องนำเอกสารนี้มาใช้งาน'),

                        Forms\Components\Select::make('status')
                            ->label('สถานะ')
                            ->options([
                                'pending' => 'รอดำเนินการ',
                                'received' => 'ได้รับแล้ว',
                                'verified' => 'ตรวจสอบแล้ว',
                                'rejected' => 'ถูกปฏิเสธ',
                                'missing' => 'ขาดหาย',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('received_at')
                            ->label('วันที่รับเอกสาร')
                            ->nullable(),

                        Forms\Components\FileUpload::make('attached_file_path')
                            ->label('ไฟล์แนบ')
                            ->helperText('ขนาดรูปไม่เกิน 3 MB, เอกสารไม่เกิน 10 MB')
                            ->disk('public')
                            ->directory('job-order-checklists')
                            ->maxSize(UploadLimits::DOCUMENT_MAX_KB)
                            ->nullable(),

                        Forms\Components\Select::make('verified_by')
                            ->label('ตรวจสอบโดย')
                            ->relationship('verifiedBy', 'name')
                            ->searchable()
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('วันที่ตรวจสอบ')
                            ->nullable(),

                        Forms\Components\Textarea::make('remark')
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
                Tables\Columns\TextColumn::make('jobOrder.job_number')
                    ->label('งาน')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('documentMaster.name')
                    ->label('เอกสาร')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobOrder.employer.company_name')
                    ->label('นายจ้าง')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label('จำเป็น')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอดำเนินการ',
                        'received' => 'ได้รับแล้ว',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
                        'missing' => 'ขาดหาย',
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
                Tables\Columns\TextColumn::make('received_at')
                    ->label('วันที่รับ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('attached_file_path')
                    ->label('ไฟล์')
                    ->boolean()
                    ->getStateUsing(fn (JobOrderChecklist $record): bool => filled($record->attached_file_path)),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('วันที่ตรวจสอบ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'received' => 'ได้รับแล้ว',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
                        'missing' => 'ขาดหาย',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('open_file')
                    ->label('เปิดไฟล์')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (JobOrderChecklist $record): ?string => $record->attached_file_path
                        ? Storage::disk('public')->url($record->attached_file_path)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (JobOrderChecklist $record): bool => filled($record->attached_file_path)),
                Tables\Actions\Action::make('verify')
                    ->label('ตรวจผ่าน')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (JobOrderChecklist $record): void {
                        $record->update([
                            'status' => 'verified',
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ]);

                        $jobOrder = $record->jobOrder()->with('checklists')->first();

                        if ($jobOrder && $jobOrder->checklists->every(fn (JobOrderChecklist $checklist): bool => $checklist->status === 'verified')) {
                            $jobOrder->update(['status' => 'approved']);
                        }

                        JobOrderLog::create([
                            'job_order_id' => $record->job_order_id,
                            'user_id' => Auth::id(),
                            'action' => 'ตรวจเอกสารผ่าน',
                            'description' => ($record->documentMaster?->name ?: 'เอกสารประกอบงาน') . ' ตรวจสอบผ่านแล้ว',
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('ส่งแก้ไข')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('remark')
                            ->label('หมายเหตุถึงนายจ้าง')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (JobOrderChecklist $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'remark' => $data['remark'],
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ]);

                        $record->jobOrder?->update(['status' => 'waiting_document']);

                        JobOrderLog::create([
                            'job_order_id' => $record->job_order_id,
                            'user_id' => Auth::id(),
                            'action' => 'เอกสารต้องแก้ไข',
                            'description' => ($record->documentMaster?->name ?: 'เอกสารประกอบงาน') . ': ' . $data['remark'],
                        ]);
                    }),
                Tables\Actions\Action::make('missing')
                    ->label('ขาดเอกสาร')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('remark')
                            ->label('หมายเหตุถึงนายจ้าง')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (JobOrderChecklist $record, array $data): void {
                        $record->update([
                            'status' => 'missing',
                            'remark' => $data['remark'],
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ]);

                        $record->jobOrder?->update(['status' => 'waiting_document']);

                        JobOrderLog::create([
                            'job_order_id' => $record->job_order_id,
                            'user_id' => Auth::id(),
                            'action' => 'ขาดเอกสาร',
                            'description' => ($record->documentMaster?->name ?: 'เอกสารประกอบงาน') . ': ' . $data['remark'],
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('received_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobOrderChecklists::route('/'),
            'create' => Pages\CreateJobOrderChecklist::route('/create'),
            'edit' => Pages\EditJobOrderChecklist::route('/{record}/edit'),
        ];
    }
}
