<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'notification_group_id',
    'notification_event_type_id',
    'is_enabled',
    'send_email',
    'send_in_app',
])]
class NotificationGroupEventSetting extends Model
{
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'send_email' => 'boolean',
            'send_in_app' => 'boolean',
        ];
    }

    public function notificationGroup(): BelongsTo
    {
        return $this->belongsTo(NotificationGroup::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(NotificationEventType::class, 'notification_event_type_id');
    }
}
