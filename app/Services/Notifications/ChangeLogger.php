<?php

namespace App\Services\Notifications;

use App\Models\ChangeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class ChangeLogger
{
    /**
     * @param  list<string>  $fields
     * @return list<ChangeLog>
     */
    public static function logChanges(
        Model $model,
        string $eventType,
        array $fields,
        ?User $actor = null,
    ): array {
        $actor ??= Auth::user() instanceof User ? Auth::user() : null;
        $logs = [];

        $changes = $model->getChanges();
        unset($changes['updated_at'], $changes['created_at']);

        foreach ($fields as $field) {
            if (! array_key_exists($field, $changes)) {
                continue;
            }

            $old = $model->getOriginal($field);
            $new = $changes[$field];

            if (self::valuesEqual($old, $new)) {
                continue;
            }

            $logs[] = ChangeLog::query()->create([
                'user_id' => $actor?->getKey(),
                'changeable_type' => $model::class,
                'changeable_id' => $model->getKey(),
                'event_type' => $eventType,
                'field_name' => $field,
                'old_value' => self::serializeValue($old),
                'new_value' => self::serializeValue($new),
            ]);
        }

        return $logs;
    }

    public static function logEvent(
        Model $model,
        string $eventType,
        ?string $fieldName = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?User $actor = null,
    ): ChangeLog {
        $actor ??= Auth::user() instanceof User ? Auth::user() : null;

        return ChangeLog::query()->create([
            'user_id' => $actor?->getKey(),
            'changeable_type' => $model::class,
            'changeable_id' => $model->getKey(),
            'event_type' => $eventType,
            'field_name' => $fieldName,
            'old_value' => self::serializeValue($oldValue),
            'new_value' => self::serializeValue($newValue),
        ]);
    }

    public static function serializeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    private static function valuesEqual(mixed $old, mixed $new): bool
    {
        return self::serializeValue($old) === self::serializeValue($new);
    }
}
