<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\DocumentReviewResource\Pages;
use App\Models\JobOrderChecklist;
use App\Models\JobOrderLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentReviewResource extends Resource
{
    protected static ?string $model = JobOrderChecklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'ตรวจเอกสาร';

    protected static ?string $navigationGroup = 'งานปฏิบัติการ';

    protected static ?string $modelLabel = 'รายการตรวจเอกสาร';

    protected static ?string $pluralModelLabel = 'รายการตรวจเอกสาร';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['jobOrder.employer', 'jobOrder.worker', 'documentMaster'])
            ->whereIn('status', ['pending', 'received', 'missing', 'rejected']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ผลการตรวจเอกสาร')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('สถานะ')
                            ->options([
                                'pending' => 'รอเอกสาร',
                                'received' => 'ได้รับแล้ว',
                                'verified' => 'ตรวจสอบแล้ว',
                                'rejected' => 'ถูกปฏิเสธ',
                                'missing' => 'ขาดเอกสาร',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('remark')
                            ->label('หมายเหตุถึงนายจ้าง')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jobOrder.job_number')
                    ->label('เลขงาน')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('jobOrder.employer.company_name')
                    ->label('นายจ้าง')
                    ->searchable()
                    ->limit(28),
                Tables\Columns\TextColumn::make('jobOrder.worker.full_name_th')
                    ->label('แรงงาน')
                    ->searchable(['first_name_th', 'last_name_th', 'first_name_en', 'last_name_en'])
                    ->limit(28),
                Tables\Columns\TextColumn::make('documentMaster.name')
                    ->label('เอกสาร')
                    ->searchable()
                    ->limit(32),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอเอกสาร',
                        'received' => 'ได้รับแล้ว',
                        'verified' => 'ตรวจสอบแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
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
                Tables\Columns\IconColumn::make('attached_file_path')
                    ->label('ไฟล์')
                    ->boolean()
                    ->getStateUsing(fn (JobOrderChecklist $record): bool => filled($record->attached_file_path)),
                Tables\Columns\TextColumn::make('received_at')
                    ->label('รับเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอเอกสาร',
                        'received' => 'ได้รับแล้ว',
                        'rejected' => 'ถูกปฏิเสธ',
                        'missing' => 'ขาดเอกสาร',
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
                Tables\Actions\EditAction::make()
                    ->label('แก้สถานะ'),
            ])
            ->defaultSort('received_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentReviews::route('/'),
            'edit' => Pages\EditDocumentReview::route('/{record}/edit'),
        ];
    }
}
