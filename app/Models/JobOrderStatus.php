<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOrderStatus extends Model
{
    protected $fillable = [
        'code',
        'name_th',
        'name_en',
        'badge_class',
        'sort_order',
        'is_active',
        'is_default',
        'requires_note',
        'sets_completed_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'requires_note' => 'boolean',
        'sets_completed_at' => 'boolean',
    ];

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class, 'status', 'code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
