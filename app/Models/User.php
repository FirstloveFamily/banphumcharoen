<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'line_user_id',
        'enable_email_notifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function employers(): BelongsToMany
    {
        return $this->belongsToMany(Employer::class, 'employer_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function primaryEmployer(): ?Employer
    {
        return $this->employers()->orderBy('company_name')->first();
    }

    public function assignedJobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class, 'assigned_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Scopes
    public function scopeAdmin($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'admin']);
        });
    }

    public function scopeByRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    // Methods
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function isEmployer(): bool
    {
        return $this->hasRole('employer');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasAnyRole(['super_admin', 'admin', 'manager']);
        }

        if ($panel->getId() === 'staff') {
            return $this->hasAnyRole(['staff', 'accounting']);
        }

        return false;
    }
}
