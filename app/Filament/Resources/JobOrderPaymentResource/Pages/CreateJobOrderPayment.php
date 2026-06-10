<?php

namespace App\Filament\Resources\JobOrderPaymentResource\Pages;

use App\Filament\Resources\JobOrderPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobOrderPayment extends CreateRecord
{
    protected static string $resource = JobOrderPaymentResource::class;

    protected function afterCreate(): void
    {
        $this->record->jobOrder?->syncPaymentSummary();
    }
}
