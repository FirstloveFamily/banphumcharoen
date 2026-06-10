<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceChecklist extends Model
{
    protected $fillable = [
        'service_id',
        'document_name',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
