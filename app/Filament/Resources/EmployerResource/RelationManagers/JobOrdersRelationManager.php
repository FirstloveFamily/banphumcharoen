<?php

namespace App\Filament\Resources\EmployerResource\RelationManagers;

use App\Filament\Resources\EmployerResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class JobOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'jobOrders';

    protected static ?string $recordTitleAttribute = 'job_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_number')
                    ->label('เลขที่งาน')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('บริการ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('worker.full_name_th')
                    ->label('แรงงาน')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('ผู้รับผิดชอบ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะงาน')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอดำเนินการ',
                        'processing' => 'กำลังดำเนินการ',
                        'waiting_document' => 'รอเอกสาร',
                        'approved' => 'อนุมัติ',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                        'rejected' => 'ปฏิเสธ',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'waiting_document' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('สถานะการชำระ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอดำเนินการ',
                        'partial' => 'ชำระบางส่วน',
                        'paid' => 'ชำระแล้ว',
                        'cancelled' => 'ยกเลิก',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'partial' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('วันครบกำหนด')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะงาน')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'processing' => 'กำลังดำเนินการ',
                        'waiting_document' => 'รอเอกสาร',
                        'approved' => 'อนุมัติ',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                        'rejected' => 'ปฏิเสธ',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('สถานะการชำระ')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'partial' => 'ชำระบางส่วน',
                        'paid' => 'ชำระแล้ว',
                        'cancelled' => 'ยกเลิก',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('สร้างงานใหม่'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
