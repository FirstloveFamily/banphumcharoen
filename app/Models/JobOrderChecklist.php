<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderChecklist extends Model
{
    protected $fillable = [
        'job_order_id',
        'document_master_id',
        'is_required',
        'status',
        'received_at',
        'attached_file_path',
        'verified_by',
        'verified_at',
        'remark',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'received_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function documentMaster(): BelongsTo
    {
        return $this->belongsTo(DocumentMaster::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
