<?php

namespace App\Models;

use App\Enums\NotificationEventCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'category', 'is_active'])]
class NotificationEventType extends Model
{
    protected function casts(): array
    {
        return [
            'category' => NotificationEventCategory::class,
            'is_active' => 'boolean',
        ];
    }

    public function groupSettings(): HasMany
    {
        return $this->hasMany(NotificationGroupEventSetting::class);
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }
}
