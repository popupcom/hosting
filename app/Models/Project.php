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

class Project extends Model
{
    use Concerns\HasIntegrationSyncStates;

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
            ->withPivotCasts(['is_primary' => 'boolean'])
            ->withTimestamps();
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class)
            ->withTimestamps();
    }

    public function supportPackage(): HasOne
    {
        return $this->hasOne(SupportPackage::class);
    }

    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    public function costLineItems(): HasMany
    {
        return $this->hasMany(CostLineItem::class);
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
