<?php

namespace App\Filament\Resources\JobOrderChecklistResource\Pages;

use App\Filament\Resources\JobOrderChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobOrderChecklists extends ListRecords
{
    protected static string $resource = JobOrderChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('สร้างรายการเช็คลิสต์'),
        ];
    }
}
