<?php

namespace App\Filament\Resources\NewsPostResource\Pages;

use App\Filament\Resources\NewsPostResource;
use Filament\Resources\Pages\EditRecord;

class EditNewsPost extends EditRecord
{
    protected static string $resource = NewsPostResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
