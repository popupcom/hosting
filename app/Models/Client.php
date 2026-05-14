<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use Concerns\HasIntegrationSyncStates;

    #[Fillable([
        'name',
        'company',
        'email',
        'phone',
        'address',
        'moco_customer_id',
        'status',
        'slug',
        'notes',
    ])]
    protected static function booted(): void
    {
        static::saving(function (Client $client): void {
            if ($client->email === '') {
                $client->email = null;
            }
            if ($client->moco_customer_id === '') {
                $client->moco_customer_id = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
        ];
    }

    public function popupApplications(): HasMany
    {
        return $this->hasMany(PopupApplication::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function costLineItems(): HasMany
    {
        return $this->hasMany(CostLineItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ClientStatus::Active);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', ClientStatus::Inactive);
    }

    public function scopeLeads(Builder $query): Builder
    {
        return $query->where('status', ClientStatus::Lead);
    }
}
