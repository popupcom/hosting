<?php

namespace App\Services\Notifications;

use App\Models\ChangeLog;
use App\Models\NotificationDispatch;
use App\Models\NotificationEventType;
use App\Models\User;
use App\Notifications\SystemChangeNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

final class NotificationDispatcher
{
    /**
     * @param  array<string, mixed>  $context
     * @param  list<ChangeLog>|null  $changeLogs
     */
    public static function dispatch(
        string $eventKey,
        Model $subject,
        array $context,
        ?array $changeLogs = null,
    ): void {
        $eventType = NotificationEventType::query()->where('key', $eventKey)->first();

        if ($eventType === null || ! $eventType->is_active) {
            return;
        }

        $primaryChange = $changeLogs[0] ?? null;
        $recipients = NotificationRecipientResolver::resolve($eventKey);

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $row) {
            /** @var User $user */
            $user = $row['user'];
            $dedupeKey = self::buildDedupeKey($eventKey, $user, $subject, $primaryChange);

            if (self::wasRecentlyDispatched($dedupeKey)) {
                continue;
            }

            try {
                $notification = new SystemChangeNotification(
                    eventType: $eventType,
                    subject: $subject,
                    context: $context,
                    sendEmail: (bool) $row['send_email'],
                    sendInApp: (bool) $row['send_in_app'],
                    primaryChange: $primaryChange,
                );

                $user->notify($notification);

                NotificationDispatch::query()->create([
                    'dedupe_key' => $dedupeKey,
                    'notification_event_type_id' => $eventType->id,
                    'user_id' => $user->getKey(),
                    'change_log_id' => $primaryChange?->getKey(),
                    'sent_email' => (bool) $row['send_email'],
                    'sent_in_app' => (bool) $row['send_in_app'],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Notification dispatch failed', [
                    'event' => $eventKey,
                    'user_id' => $user->getKey(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public static function buildDedupeKey(
        string $eventKey,
        User $user,
        Model $subject,
        ?ChangeLog $change = null,
    ): string {
        $parts = [
            $eventKey,
            $user->getKey(),
            $subject::class,
            (string) $subject->getKey(),
            $change?->field_name ?? 'event',
            $change?->new_value ?? '',
        ];

        return hash('sha256', implode('|', $parts));
    }

    public static function wasRecentlyDispatched(string $dedupeKey): bool
    {
        $minutes = config('notifications.dedupe_minutes', 60);

        return NotificationDispatch::query()
            ->where('dedupe_key', $dedupeKey)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }

    /**
     * @param  list<ChangeLog>  $logs
     * @return list<array{field: string, old: ?string, new: ?string, label: string}>
     */
    public static function changesFromLogs(array $logs): array
    {
        return collect($logs)
            ->map(fn (ChangeLog $log): array => [
                'field' => (string) $log->field_name,
                'label' => self::fieldLabel((string) $log->field_name),
                'old' => $log->old_value,
                'new' => $log->new_value,
            ])
            ->values()
            ->all();
    }

    public static function fieldLabel(string $field): string
    {
        return match ($field) {
            'status' => 'Status',
            'custom_sales_price', 'sales_price_snapshot' => 'VK',
            'custom_cost_price', 'cost_price_snapshot' => 'EK',
            'custom_billing_interval', 'billing_interval_snapshot' => 'Verrechnungsintervall',
            'billing_group_id' => 'Verrechnungsgruppe',
            'end_date' => 'Enddatum',
            'cancellation_date' => 'Kündigungsdatum',
            'do_not_renew' => 'Nicht verlängern',
            'moco_sync_status' => 'Moco-Sync',
            'quantity', 'custom_quantity' => 'Menge',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }
}
