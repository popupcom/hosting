<?php

namespace App\Models;

use App\Enums\ProjectSupportPackageStatus;
use App\Services\SupportPackages\SupportPackageProjectServiceProvisioner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'support_package_id',
    'project_service_id',
    'start_date',
    'end_date',
    'cancellation_date',
    'do_not_renew',
    'status',
    'notes',
])]
class ProjectSupportPackage extends Model
{
    protected static function booted(): void
    {
        static::creating(function (ProjectSupportPackage $assignment): void {
            $assignment->start_date ??= now()->toDateString();
            if ($assignment->status === null) {
                $assignment->status = ProjectSupportPackageStatus::Active;
            }
        });

        static::saving(function (ProjectSupportPackage $assignment): void {
            if ($assignment->do_not_renew && $assignment->status === ProjectSupportPackageStatus::Active) {
                $assignment->status = ProjectSupportPackageStatus::PendingCancellation;
            }

            if (
                $assignment->end_date !== null
                && $assignment->end_date->lte(now()->startOfDay())
                && in_array($assignment->status, [ProjectSupportPackageStatus::Active, ProjectSupportPackageStatus::PendingCancellation], true)
            ) {
                $assignment->status = ProjectSupportPackageStatus::Expired;
            }
        });

        static::saved(function (ProjectSupportPackage $assignment): void {
            if ($assignment->status === ProjectSupportPackageStatus::Active) {
                SupportPackageProjectServiceProvisioner::syncActiveAssignment($assignment);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ProjectSupportPackageStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'cancellation_date' => 'date',
            'do_not_renew' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supportPackage(): BelongsTo
    {
        return $this->belongsTo(SupportPackage::class);
    }

    public function projectService(): BelongsTo
    {
        return $this->belongsTo(ProjectService::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProjectSupportPackageStatus::Active);
    }

    public function scopeCountsAsActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ProjectSupportPackageStatus::Active->value,
            ProjectSupportPackageStatus::PendingCancellation->value,
        ]);
    }
}
