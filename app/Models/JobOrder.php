<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_number',
        'employer_id',
        'worker_id',
        'service_id',
        'assigned_user_id',
        'service_fee',
        'paid_amount',
        'payment_status',
        'status',
        'priority',
        'due_date',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'service_fee' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($jobOrder) {
            if (empty($jobOrder->job_number)) {
                $prefix = 'AP' . date('ymd');
                
                $latestJob = static::withTrashed()
                    ->where('job_number', 'like', $prefix . '%')
                    ->orderBy('job_number', 'desc')
                    ->first();

                if ($latestJob) {
                    $lastNumber = intval(substr($latestJob->job_number, -3));
                    $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $nextNumber = '001';
                }

                $jobOrder->job_number = $prefix . $nextNumber;
            }
        });
    }

    // Relationships
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(JobOrderChecklist::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(JobOrderPayment::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(JobOrderLog::class);
    }

    public function deliverySheetItems(): HasMany
    {
        return $this->hasMany(DeliverySheetItem::class);
    }

    public function statusDefinition(): BelongsTo
    {
        return $this->belongsTo(JobOrderStatus::class, 'status', 'code');
    }

    // Methods
    public function getRemainingAmount(): float
    {
        return max(floatval($this->service_fee) - floatval($this->paid_amount), 0);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusDefinition?->name_th ?? match ($this->status) {
            'pending' => 'รอเริ่มงาน',
            'processing' => 'กำลังดำเนินการ',
            'waiting_document' => 'รอเอกสาร',
            'approved' => 'อนุมัติแล้ว',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
            'rejected' => 'ไม่ผ่าน',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->statusDefinition?->badge_class ?? match ($this->status) {
            'pending' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'processing' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            'waiting_document' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'completed' => 'bg-slate-900 text-white ring-slate-900/20',
            'cancelled' => 'bg-slate-100 text-slate-500 ring-slate-400/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public function syncPaymentSummary(): void
    {
        $verifiedPaidAmount = (float) $this->payments()
            ->where('status', 'verified')
            ->sum('amount');

        $serviceFee = (float) $this->service_fee;

        $paymentStatus = match (true) {
            $verifiedPaidAmount <= 0 => 'pending',
            $serviceFee > 0 && $verifiedPaidAmount < $serviceFee => 'partial',
            default => 'paid',
        };

        $this->forceFill([
            'paid_amount' => $verifiedPaidAmount,
            'payment_status' => $paymentStatus,
        ])->save();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
