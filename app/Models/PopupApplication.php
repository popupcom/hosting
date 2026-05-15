<?php

namespace App\Models;

use App\Enums\PopupApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_id',
    'server_id',
    'name',
    'slug',
    'status',
    'repository_url',
    'notes',
])]
class PopupApplication extends Model
{
    protected function casts(): array
    {
        return [
            'status' => PopupApplicationStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
}
