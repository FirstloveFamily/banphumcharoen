<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySheetAttachment extends Model
{
    protected $fillable = [
        'delivery_sheet_id',
        'uploaded_by_user_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'note',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function deliverySheet(): BelongsTo
    {
        return $this->belongsTo(DeliverySheet::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
