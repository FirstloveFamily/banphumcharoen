<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentMaster extends Model
{
    protected $table = 'document_masters';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workerDocuments(): HasMany
    {
        return $this->hasMany(WorkerDocument::class);
    }

    public function jobOrderChecklists(): HasMany
    {
        return $this->hasMany(JobOrderChecklist::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(DocumentEvidence::class)->orderBy('sort_order')->orderBy('name');
    }

    public function serviceChecklists(): HasMany
    {
        return $this->hasMany(ServiceChecklist::class, 'document_name', 'name')->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
