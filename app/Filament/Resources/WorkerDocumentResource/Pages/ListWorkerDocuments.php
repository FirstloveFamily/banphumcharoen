<?php

namespace App\Filament\Resources\WorkerDocumentResource\Pages;

use App\Filament\Resources\WorkerDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkerDocuments extends ListRecords
{
    protected static string $resource = WorkerDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('สร้างเอกสารใหม่'),
        ];
    }
}
