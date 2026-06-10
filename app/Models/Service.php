<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'alert_days_before_expiry',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function checklists(): HasMany
    {
        return $this->hasMany(ServiceChecklist::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
