<?php

namespace App\Services\Notifications;

use App\Models\NotificationEventType;
use App\Models\NotificationGroupEventSetting;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Collection;

final class NotificationRecipientResolver
{
    /**
     * @return Collection<int, array{user: User, send_email: bool, send_in_app: bool}>
     */
    public static function resolve(string $eventKey): Collection
    {
        $eventType = NotificationEventType::query()
            ->where('key', $eventKey)
            ->where('is_active', true)
            ->first();

        if ($eventType === null) {
            return collect();
        }

        /** @var array<int, array{user: User, send_email: bool, send_in_app: bool}> $recipients */
        $recipients = [];

        NotificationGroupEventSetting::query()
            ->where('notification_event_type_id', $eventType->id)
            ->where('is_enabled', true)
            ->whereHas('notificationGroup', fn ($q) => $q->where('is_active', true))
            ->with(['notificationGroup.users' => fn ($q) => $q->where('is_active', true)])
            ->each(function (NotificationGroupEventSetting $setting) use (&$recipients): void {
                foreach ($setting->notificationGroup->users as $user) {
                    $recipients[$user->getKey()] = [
                        'user' => $user,
                        'send_email' => $setting->send_email,
                        'send_in_app' => $setting->send_in_app,
                    ];
                }
            });

        UserNotificationPreference::query()
            ->where('notification_event_type_id', $eventType->id)
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->each(function (UserNotificationPreference $pref) use (&$recipients): void {
                $user = $pref->user;
                $id = $user->getKey();

                if (! $pref->is_enabled) {
                    unset($recipients[$id]);

                    return;
                }

                $recipients[$id] = [
                    'user' => $user,
                    'send_email' => $pref->email_enabled,
                    'send_in_app' => $pref->in_app_enabled,
                ];
            });

        return collect($recipients)->filter(
            fn (array $row): bool => $row['send_email'] || $row['send_in_app']
        );
    }
}
