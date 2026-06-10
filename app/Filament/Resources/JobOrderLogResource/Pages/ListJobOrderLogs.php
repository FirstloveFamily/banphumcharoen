<?php

namespace App\Filament\Resources\JobOrderLogResource\Pages;

use App\Filament\Resources\JobOrderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobOrderLogs extends ListRecords
{
    protected static string $resource = JobOrderLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('สร้างบันทึกกิจกรรม'),
        ];
    }
}
