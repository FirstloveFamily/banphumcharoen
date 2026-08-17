<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class WorkerDocument extends Model
{
    protected $fillable = [
        'worker_id',
        'document_master_id',
        'file_path',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
        'expiry_date',
        'note',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function documentMaster(): BelongsTo
    {
        return $this->belongsTo(DocumentMaster::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function expiringIn(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }
}
