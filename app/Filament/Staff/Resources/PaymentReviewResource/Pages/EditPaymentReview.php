<?php

namespace App\Filament\Staff\Resources\PaymentReviewResource\Pages;

use App\Filament\Staff\Resources\PaymentReviewResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPaymentReview extends EditRecord
{
    protected static string $resource = PaymentReviewResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? null, ['verified', 'rejected'], true)) {
            $data['received_by'] = Auth::id();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->jobOrder?->syncPaymentSummary();
    }
}
