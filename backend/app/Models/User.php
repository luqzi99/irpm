<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'subscription_plan',
        'subscription_expires_at',
        'is_active',
        'email_verified_at',
        'verification_token',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Subscription plan limits
    public const PLAN_LIMITS = [
        'free' => [
            'classes' => 1,
            'students_per_class' => 30,
            'schedules' => 5,
            'can_export' => false,
        ],
        'basic' => [
            'classes' => 5,
            'students_per_class' => 50,
            'schedules' => 20,
            'can_export' => true,
        ],
        'pro' => [
            'classes' => PHP_INT_MAX,
            'students_per_class' => PHP_INT_MAX,
            'schedules' => PHP_INT_MAX,
            'can_export' => true,
        ],
    ];

    // Relationships
    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'teacher_id');
    }

    public function teachingSchedules(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class, 'teacher_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Role Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    // Subscription Helpers
    public function getPlanLimits(): array
    {
        return self::PLAN_LIMITS[$this->subscription_plan] ?? self::PLAN_LIMITS['free'];
    }

    public function canCreateClass(): bool
    {
        if ($this->isAdmin()) return true; // Admin has no limits
        $limits = $this->getPlanLimits();
        return $this->classes()->count() < $limits['classes'];
    }

    public function canAddStudentToClass(): bool
    {
        if ($this->isAdmin()) return true;
        $limits = $this->getPlanLimits();
        // For simplicity, check against the max. In practice, check per-class.
        return true; // Will be checked at class level
    }

    public function canCreateSchedule(): bool
    {
        if ($this->isAdmin()) return true; // Admin has no limits
        $limits = $this->getPlanLimits();
        return $this->teachingSchedules()->count() < $limits['schedules'];
    }

    public function canExport(): bool
    {
        if ($this->isAdmin()) return true; // Admin can always export
        return $this->getPlanLimits()['can_export'];
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_plan === 'free') {
            return true; // Free plan never expires
        }
        return $this->subscription_expires_at === null || 
               $this->subscription_expires_at->isFuture();
    }

    public function getSubscriptionLabel(): string
    {
        return match($this->subscription_plan) {
            'free' => 'Percuma',
            'basic' => 'Asas',
            'pro' => 'Pro',
            default => 'Percuma',
        };
    }
}

