<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_code',
        'company_name',
        'contact_name',
        'phone',
        'email',
        'tax_id',
        'address',
        'logo',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employer_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
