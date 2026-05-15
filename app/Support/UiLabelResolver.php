<?php

namespace App\Support;

use App\Models\DesignSetting;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class UiLabelResolver
{
    /** @var array<string, array<string, string>>|null */
    private static ?array $overrides = null;

    public static function flush(): void
    {
        self::$overrides = null;
    }

    /**
     * @param  array<string, string>  $defaults
     * @return array<string, string>
     */
    public static function merge(string $group, array $defaults): array
    {
        $groupOverrides = self::all()[$group] ?? [];

        foreach ($groupOverrides as $key => $value) {
            if (! is_string($key) || ! array_key_exists($key, $defaults)) {
                continue;
            }

            if (is_string($value) && trim($value) !== '') {
                $defaults[$key] = trim($value);
            }
        }

        return $defaults;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function all(): array
    {
        if (self::$overrides !== null) {
            return self::$overrides;
        }

        try {
            if (! Schema::hasTable('design_settings')) {
                self::$overrides = [];

                return self::$overrides;
            }

            $stored = DesignSetting::current()->ui_label_overrides;

            self::$overrides = is_array($stored) ? $stored : [];
        } catch (Throwable) {
            self::$overrides = [];
        }

        return self::$overrides;
    }
}
