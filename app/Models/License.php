<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class License extends Model
{
    #[Fillable([
        'name',
        'vendor',
        'license_type',
        'license_reference',
        'max_installations',
        'used_installations',
        'expires_at',
        'cancellation_notice_days',
        'cost_price',
        'selling_price',
        'billing_interval',
        'status',
        'reminder_at',
        'lastpass_reference',
        'notes',
    ])]
    protected static function booted(): void
    {
        static::saving(function (License $license): void {
            if ($license->lastpass_reference === '') {
                $license->lastpass_reference = null;
            }
            if ($license->license_reference === '') {
                $license->license_reference = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => LicenseStatus::class,
            'expires_at' => 'date',
            'reminder_at' => 'date',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withTimestamps();
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    public function costLineItems(): MorphMany
    {
        return $this->morphMany(CostLineItem::class, 'billable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', LicenseStatus::Active);
    }
}
