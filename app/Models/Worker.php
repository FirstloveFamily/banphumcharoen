<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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

    public function passportAttachments(): Collection
    {
        $documents = $this->relationLoaded('documents')
            ? $this->documents
            : $this->documents()->with('documentMaster')->get();

        $attachments = $documents
            ->filter(fn (WorkerDocument $document): bool => $this->isPassportDocument($document))
            ->map(function (WorkerDocument $document): array {
                return [
                    'label' => $document->documentMaster?->name ?: 'ไฟล์ Passport',
                    'url' => asset('storage/' . $document->file_path),
                    'expiry_date' => $document->expiry_date,
                    'note' => $document->note,
                    'source' => 'worker_document',
                ];
            })
            ->values();

        if ($this->passport_file) {
            $attachments->push([
                'label' => 'Passport Copy',
                'url' => asset('storage/' . $this->passport_file),
                'expiry_date' => $this->passport_expiry,
                'note' => null,
                'source' => 'legacy_field',
            ]);
        }

        return $attachments;
    }

    public function passportAttachment(): ?array
    {
        return $this->passportAttachments()->first();
    }

    public function legacyAttachments(): Collection
    {
        return collect([
            ['name' => 'Passport Copy', 'file' => $this->passport_file, 'expiry_date' => $this->passport_expiry],
            ['name' => 'Work Permit Copy', 'file' => $this->wp_file, 'expiry_date' => $this->wp_expiry],
            ['name' => 'Visa Copy', 'file' => $this->visa_file, 'expiry_date' => $this->visa_expiry],
            ['name' => '90-Days Report', 'file' => $this->report_90_days_file, 'expiry_date' => $this->report_90_days_due],
        ])->map(fn (array $attachment): array => [
            'name' => $attachment['name'],
            'file' => $attachment['file'],
            'url' => $attachment['file'] ? asset('storage/' . $attachment['file']) : null,
            'expiry_date' => $attachment['expiry_date'],
        ]);
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
