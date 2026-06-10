<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentEvidence extends Model
{
    protected $table = 'document_evidences';

    protected $fillable = [
        'document_master_id',
        'name',
        'description',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function documentMaster(): BelongsTo
    {
        return $this->belongsTo(DocumentMaster::class);
    }
}
