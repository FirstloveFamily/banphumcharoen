<?php

namespace App\Filament\Resources\DocumentMasterResource\Pages;

use App\Filament\Resources\DocumentMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentMaster extends EditRecord
{
    protected static string $resource = DocumentMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
