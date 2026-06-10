<?php

namespace App\Filament\Resources\JobOrderPaymentResource\Pages;

use App\Filament\Resources\JobOrderPaymentResource;
use Filament\Resources\Pages\EditRecord;

class EditJobOrderPayment extends EditRecord
{
    protected static string $resource = JobOrderPaymentResource::class;

    protected function afterSave(): void
    {
        $this->record->jobOrder?->syncPaymentSummary();
    }
}
