<?php

namespace App\Models;

use App\Enums\ProjectDomainStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectDomain extends Model
{
    use Concerns\HasIntegrationSyncStates;

    protected $table = 'project_domains';

    #[Fillable([
        'project_id',
        'domain_name',
        'registrar',
        'hosting_provider',
        'autodns_id',
        'dns_zone',
        'nameservers',
        'expires_at',
        'cancellation_notice_days',
        'cost_price',
        'selling_price',
        'billing_interval',
        'status',
        'reminder_at',
        'notes',
    ])]
    protected static function booted(): void
    {
        static::saving(function (ProjectDomain $projectDomain): void {
            if ($projectDomain->autodns_id === '') {
                $projectDomain->autodns_id = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'reminder_at' => 'date',
            'status' => ProjectDomainStatus::class,
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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
        return $query->where('status', ProjectDomainStatus::Active);
    }

    public function scopeExpiringBefore(Builder $query, \DateTimeInterface $date): Builder
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', $date);
    }
}
