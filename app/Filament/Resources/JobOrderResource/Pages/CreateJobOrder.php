<?php

namespace App\Filament\Resources\JobOrderResource\Pages;

use App\Filament\Resources\JobOrderResource;
use App\Models\DocumentMaster;
use App\Models\JobOrderChecklist;
use App\Models\ServiceChecklist;
use Filament\Resources\Pages\CreateRecord;

class CreateJobOrder extends CreateRecord
{
    protected static string $resource = JobOrderResource::class;

    protected function afterCreate(): void
    {
        $jobOrder = $this->record;

        if (!$jobOrder->service_id) {
            return;
        }

        // Get checklist templates from selected service
        $serviceChecklists = ServiceChecklist::where('service_id', $jobOrder->service_id)
            ->orderBy('sort_order')
            ->get();

        foreach ($serviceChecklists as $checklist) {
            // Match document_name to DocumentMaster
            $documentMaster = DocumentMaster::where('name', $checklist->document_name)->first();

            JobOrderChecklist::create([
                'job_order_id' => $jobOrder->id,
                'document_master_id' => $documentMaster?->id,
                'is_required' => $checklist->is_required,
                'status' => 'pending',
            ]);
        }
    }
}
