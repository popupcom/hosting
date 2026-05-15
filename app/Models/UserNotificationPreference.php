<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'notification_event_type_id',
    'is_enabled',
    'email_enabled',
    'in_app_enabled',
])]
class UserNotificationPreference extends Model
{
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(NotificationEventType::class, 'notification_event_type_id');
    }
}
