<?php

namespace App\Support;

final class NotificationStyleTokens
{
    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        /** @var array<string, string> $tokens */
        $tokens = config('filament-brand.notification_tokens', []);

        return $tokens;
    }

    /**
     * @return list<string>
     */
    public static function primaryFallbackKeys(): array
    {
        /** @var list<string> $keys */
        $keys = config('filament-brand.notification_primary_fallbacks', []);

        return $keys;
    }

    /**
     * @param  array<string, string|null>|null  $overrides
     * @return array<string, string>
     */
    public static function resolve(?array $overrides, string $primaryColor): array
    {
        $resolved = self::defaults();

        if (is_array($overrides)) {
            foreach ($overrides as $key => $value) {
                if (! is_string($key) || ! array_key_exists($key, $resolved)) {
                    continue;
                }

                if (is_string($value) && trim($value) !== '') {
                    $resolved[$key] = trim($value);
                }
            }
        }

        foreach (self::primaryFallbackKeys() as $key) {
            if (trim($resolved[$key] ?? '') !== '') {
                continue;
            }

            $resolved[$key] = $primaryColor;
        }

        return $resolved;
    }

    /**
     * @return array<string, array{label: string, helper: string|null, type: string}>
     */
    public static function fieldDefinitions(): array
    {
        /** @var array<string, array{label: string, helper: string|null, type: string}> $fields */
        $fields = config('filament-brand.notification_fields', []);

        return $fields;
    }
}
