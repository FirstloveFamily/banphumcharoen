<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySheetItem extends Model
{
    protected $fillable = [
        'delivery_sheet_id',
        'job_order_id',
        'note',
    ];

    public function deliverySheet(): BelongsTo
    {
        return $this->belongsTo(DeliverySheet::class);
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }
}
