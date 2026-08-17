<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerRegistrationRequest extends Model
{
    protected $fillable = [
        'employer_id',
        'requested_by',
        'reviewed_by',
        'status',
        'data',
        'review_note',
        'reviewed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
