<?php

namespace App\Filament\Resources\ServiceChecklistResource\Pages;

use App\Filament\Resources\ServiceChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceChecklists extends ListRecords
{
    protected static string $resource = ServiceChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('สร้างเช็คลิสต์'),
        ];
    }
}
