<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nationality extends Model
{
    protected $fillable = [
        'name_th',
        'name_en',
        'country_code',
        'icon_flag',
        'is_active',
    ];

    protected $casts = [
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
