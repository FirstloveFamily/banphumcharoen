<?php

namespace App\Filament\Resources\JobOrderPaymentResource\Pages;

use App\Filament\Resources\JobOrderPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobOrderPayments extends ListRecords
{
    protected static string $resource = JobOrderPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('สร้างรายการชำระเงิน'),
        ];
    }
}
