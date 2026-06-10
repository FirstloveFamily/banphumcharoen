<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySheet extends Model
{
    protected $fillable = [
        'employer_id',
        'created_by_user_id',
        'sheet_number',
        'sheet_date',
        'status',
        'note',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'completed_at',
    ];

    protected $casts = [
        'sheet_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $deliverySheet): void {
            if (blank($deliverySheet->sheet_number)) {
                $prefix = 'DS' . date('ymd');

                $latest = static::query()
                    ->where('sheet_number', 'like', $prefix . '%')
                    ->orderByDesc('sheet_number')
                    ->first();

                $nextNumber = '001';

                if ($latest) {
                    $lastNumber = intval(substr($latest->sheet_number, -3));
                    $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                }

                $deliverySheet->sheet_number = $prefix . $nextNumber;
            }
        });

        static::saving(function (self $deliverySheet): void {
            if ($deliverySheet->status === 'submitted' && blank($deliverySheet->submitted_at)) {
                $deliverySheet->submitted_at = now();
            }

            if ($deliverySheet->status === 'approved' && blank($deliverySheet->approved_at)) {
                $deliverySheet->approved_at = now();
            }

            if ($deliverySheet->status === 'rejected' && blank($deliverySheet->rejected_at)) {
                $deliverySheet->rejected_at = now();
            }

            if ($deliverySheet->status === 'completed' && blank($deliverySheet->completed_at)) {
                $deliverySheet->completed_at = now();
            }
        });
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliverySheetItem::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DeliverySheetAttachment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'ร่าง',
            'submitted' => 'ส่งแล้ว',
            'approved' => 'อนุมัติ',
            'rejected' => 'ไม่อนุมัติ',
            'completed' => 'เสร็จสิ้น',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-100 text-slate-600 ring-slate-400/15',
            'submitted' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            'completed' => 'bg-slate-900 text-white ring-slate-900/20',
            default => 'bg-slate-100 text-slate-600',
        };
    }
}
