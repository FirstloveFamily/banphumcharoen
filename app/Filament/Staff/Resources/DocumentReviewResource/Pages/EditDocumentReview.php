<?php

namespace App\Filament\Staff\Resources\DocumentReviewResource\Pages;

use App\Filament\Staff\Resources\DocumentReviewResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDocumentReview extends EditRecord
{
    protected static string $resource = DocumentReviewResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? null, ['verified', 'rejected', 'missing'], true)) {
            $data['verified_by'] = Auth::id();
            $data['verified_at'] = now();
        }

        return $data;
    }
}
