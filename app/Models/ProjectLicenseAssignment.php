<?php

namespace App\Models;

use App\Enums\LicenseAssignmentStatus;
use App\Support\LicenseCodeRules;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'license_product_id',
    'project_id',
    'client_id',
    'license_code',
    'assigned_at',
    'activated_at',
    'cancelled_at',
    'cancellation_effective_date',
    'cancellation_reason',
    'do_not_renew',
    'status',
    'notes',
])]
class ProjectLicenseAssignment extends Model
{
    protected $table = 'project_license_assignments';

    protected static function booted(): void
    {
        static::creating(function (ProjectLicenseAssignment $assignment): void {
            $assignment->assigned_at ??= now();
            if ($assignment->activated_at === null && $assignment->status === LicenseAssignmentStatus::Active) {
                $assignment->activated_at = now();
            }
            if ($assignment->client_id === null && $assignment->project_id) {
                $assignment->client_id = Project::query()->whereKey($assignment->project_id)->value('client_id');
            }
        });

        static::saving(function (ProjectLicenseAssignment $assignment): void {
            LicenseCodeRules::validateAssignment($assignment);

            if ($assignment->do_not_renew) {
                if ($assignment->status === LicenseAssignmentStatus::Active) {
                    $assignment->status = LicenseAssignmentStatus::PendingCancellation;
                }
            }

            if (
                $assignment->cancellation_effective_date !== null
                && $assignment->cancellation_effective_date->lte(now()->startOfDay())
                && in_array($assignment->status, [LicenseAssignmentStatus::Active, LicenseAssignmentStatus::PendingCancellation], true)
            ) {
                $assignment->status = LicenseAssignmentStatus::Expired;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => LicenseAssignmentStatus::class,
            'assigned_at' => 'datetime',
            'activated_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'cancellation_effective_date' => 'date',
            'do_not_renew' => 'boolean',
        ];
    }

    public function licenseProduct(): BelongsTo
    {
        return $this->belongsTo(LicenseProduct::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', LicenseAssignmentStatus::Active);
    }

    public function scopeCountsAsUsed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            LicenseAssignmentStatus::Active->value,
            LicenseAssignmentStatus::PendingCancellation->value,
        ]);
    }

    public function effectiveLicenseCode(): ?string
    {
        return LicenseCodeRules::effectiveCode($this);
    }
}
