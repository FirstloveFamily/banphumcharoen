<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employer_id',
        'nationality_id',
        'worker_prefix_id',
        'prefix_th',
        'first_name_th',
        'last_name_th',
        'prefix_en',
        'first_name_en',
        'last_name_en',
        'birth_date',
        'gender',
        'passport_number',
        'passport_expiry',
        'wp_number',
        'wp_expiry',
        'visa_expiry',
        'report_90_days_due',
        'passport_file',
        'wp_file',
        'visa_file',
        'report_90_days_file',
        'photo_path',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'passport_expiry' => 'date',
        'wp_expiry' => 'date',
        'visa_expiry' => 'date',
        'report_90_days_due' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $worker): void {
            if ($worker->worker_prefix_id) {
                $workerPrefix = $worker->relationLoaded('workerPrefix')
                    ? $worker->workerPrefix
                    : WorkerPrefix::find($worker->worker_prefix_id);

                $worker->prefix_th = $workerPrefix?->name_th;
                $worker->prefix_en = $workerPrefix?->name_en;
            } elseif ($worker->worker_prefix_id === null) {
                $worker->prefix_th = $worker->prefix_th ?: null;
                $worker->prefix_en = $worker->prefix_en ?: null;
            }
        });
    }

    // Relationships
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class);
    }

    public function workerPrefix(): BelongsTo
    {
        return $this->belongsTo(WorkerPrefix::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(WorkerDocument::class);
    }

    public function isPassportDocument(WorkerDocument $document): bool
    {
        $code = strtoupper((string) $document->documentMaster?->code);
        $name = mb_strtolower((string) ($document->documentMaster?->name ?? ''));

        return $code === 'PASSPORT'
            || str_contains($name, 'passport')
            || str_contains($name, 'หนังสือเดินทาง');
    }

    public function passportAttachment(): ?array
    {
        $documents = $this->relationLoaded('documents')
            ? $this->documents
            : $this->documents()->with('documentMaster')->get();

        $passportDocument = $documents->first(fn (WorkerDocument $document): bool => $this->isPassportDocument($document));

        if ($passportDocument) {
            return [
                'label' => $passportDocument->documentMaster?->name ?: 'ไฟล์ Passport',
                'url' => asset('storage/' . $passportDocument->file_path),
                'expiry_date' => $passportDocument->expiry_date,
                'note' => $passportDocument->note,
                'source' => 'worker_document',
            ];
        }

        if ($this->passport_file) {
            return [
                'label' => 'Passport Copy',
                'url' => asset('storage/' . $this->passport_file),
                'expiry_date' => $this->passport_expiry,
                'note' => null,
                'source' => 'legacy_field',
            ];
        }

        return null;
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    // Accessors
    public function getFullNameThAttribute(): string
    {
        $prefix = $this->workerPrefix?->name_th ?? $this->prefix_th ?? '';

        return trim($prefix . ' ' . $this->first_name_th . ' ' . $this->last_name_th);
    }

    public function getFullNameEnAttribute(): string
    {
        $prefix = $this->workerPrefix?->name_en ?? $this->prefix_en ?? '';

        return trim($prefix . ' ' . $this->first_name_en . ' ' . $this->last_name_en);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
