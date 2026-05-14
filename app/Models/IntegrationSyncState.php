<?php

namespace App\Models;

use App\Enums\IntegrationProvider;
use App\Enums\MocoSyncStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IntegrationSyncState extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'syncable_type',
        'syncable_id',
        'provider',
        'status',
        'external_id',
        'last_synced_at',
        'last_error',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'provider' => IntegrationProvider::class,
            'status' => MocoSyncStatus::class,
            'last_synced_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }
}
