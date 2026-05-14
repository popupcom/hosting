<?php

namespace App\Models;

use App\Enums\IntegrationProvider;
use Illuminate\Database\Eloquent\Model;

class IntegrationWebhookEvent extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'dedupe_key',
        'payload',
        'ip_address',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => IntegrationProvider::class,
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
