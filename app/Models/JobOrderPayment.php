<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class JobOrderPayment extends Model
{
    protected $fillable = [
        'job_order_id',
        'amount',
        'payment_date',
        'payment_method',
        'payment_reference',
        'slip_path',
        'status',
        'received_by',
        'note',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (JobOrderPayment $payment): void {
            if ($payment->status === 'verified' && blank($payment->received_by) && Auth::check()) {
                $payment->received_by = Auth::id();
            }
        });

        static::saved(function (JobOrderPayment $payment): void {
            $payment->jobOrder?->syncPaymentSummary();
        });

        static::deleted(function (JobOrderPayment $payment): void {
            $payment->jobOrder?->syncPaymentSummary();
        });
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }
}
