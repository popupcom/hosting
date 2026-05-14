<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingProvider extends Model
{
    #[Fillable(['name', 'slug', 'website_url', 'has_api', 'notes'])]
    protected function casts(): array
    {
        return [
            'has_api' => 'boolean',
        ];
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
