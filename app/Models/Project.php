<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'client_id',
    'name',
    'url',
    'wordpress_version',
    'php_version',
    'managewp_site_id',
    'lastpass_reference',
    'moco_project_id',
    'status',
    'maintenance_contract',
    'notes',
])]
class Project extends Model
{
    use Concerns\HasIntegrationSyncStates;

    protected static function booted(): void
    {
        static::saving(function (Project $project): void {
            if ($project->managewp_site_id === '') {
                $project->managewp_site_id = null;
            }
            if ($project->moco_project_id === '') {
                $project->moco_project_id = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'maintenance_contract' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(ProjectDomain::class);
    }

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function projectLicenses(): HasMany
    {
        return $this->hasMany(ProjectLicenseAssignment::class);
    }

    public function licenseAssignments(): HasMany
    {
        return $this->projectLicenses();
    }

    public function projectServices(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }

    public function supportPackage(): HasOne
    {
        return $this->hasOne(SupportPackage::class);
    }

    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    public function billingGroups(): HasMany
    {
        return $this->hasMany(BillingGroup::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Active);
    }

    public function scopeWithMaintenanceContract(Builder $query): Builder
    {
        return $query->where('maintenance_contract', true);
    }
}
