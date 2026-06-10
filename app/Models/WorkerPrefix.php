<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerPrefix extends Model
{
    protected $fillable = [
        'code',
        'name_th',
        'name_en',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
