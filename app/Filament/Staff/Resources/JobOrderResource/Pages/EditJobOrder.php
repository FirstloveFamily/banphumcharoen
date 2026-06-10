<?php

namespace App\Filament\Staff\Resources\JobOrderResource\Pages;

use App\Filament\Staff\Resources\JobOrderResource;
use App\Models\JobOrderLog;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditJobOrder extends EditRecord
{
    protected static string $resource = JobOrderResource::class;

    protected function afterSave(): void
    {
        JobOrderLog::create([
            'job_order_id' => $this->record->id,
            'user_id' => Auth::id(),
            'action' => 'เจ้าหน้าที่อัปเดตใบงาน',
            'description' => 'อัปเดตสถานะหรือรายละเอียดใบงานจาก Staff Panel',
        ]);
    }
}
