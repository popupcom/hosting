<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'popup_application_id',
    'hostname',
    'ssl_expires_at',
    'is_primary',
    'notes',
])]
class Domain extends Model
{
    protected function casts(): array
    {
        return [
            'ssl_expires_at' => 'datetime',
            'is_primary' => 'boolean',
        ];
    }

    public function popupApplication(): BelongsTo
    {
        return $this->belongsTo(PopupApplication::class);
    }
}
