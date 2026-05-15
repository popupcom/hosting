<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDispatch extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sent_email' => 'boolean',
            'sent_in_app' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(NotificationEventType::class, 'notification_event_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changeLog(): BelongsTo
    {
        return $this->belongsTo(ChangeLog::class);
    }
}
