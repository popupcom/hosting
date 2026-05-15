<?php

namespace App\Models;

use App\Enums\ServerStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'hosting_provider_id',
    'name',
    'hostname',
    'ip_address',
    'region',
    'status',
    'notes',
    'operating_system',
    'php_versions',
    'contract_expires_at',
    'cancellation_notice_days',
    'cost_price',
    'selling_price',
    'billing_interval',
    'lastpass_reference',
])]
class Server extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Server $server): void {
            if ($server->lastpass_reference === '') {
                $server->lastpass_reference = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ServerStatus::class,
            'php_versions' => 'array',
            'contract_expires_at' => 'date',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function hostingProvider(): BelongsTo
    {
        return $this->belongsTo(HostingProvider::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    public function popupApplications(): HasMany
    {
        return $this->hasMany(PopupApplication::class);
    }
}
