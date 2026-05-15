<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'app_name',
    'ui_locale',
    'primary_color',
    'secondary_color',
    'accent_color',
    'background_color',
    'text_color',
    'border_radius',
    'logo_path',
    'favicon_path',
    'custom_css',
    'design_notes',
    'updated_by',
])]
class DesignSetting extends Model
{
    private static ?self $rememberedInstance = null;

    public static function forgetRememberedInstance(): void
    {
        self::$rememberedInstance = null;
    }

    /**
     * Singleton: ein Datensatz pro Anwendung (unique singleton_key).
     */
    public static function current(): self
    {
        if (self::$rememberedInstance !== null) {
            return self::$rememberedInstance;
        }

        return self::$rememberedInstance = static::unguarded(function (): self {
            return static::query()->firstOrCreate(
                ['singleton_key' => 'app'],
                static::defaultAttributes(),
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultAttributes(): array
    {
        $brand = config('filament-brand', []);

        return [
            'app_name' => config('app.name'),
            'ui_locale' => 'de',
            'primary_color' => $brand['colors']['primary'] ?? '#d6002a',
            'secondary_color' => '#52525b',
            'accent_color' => $brand['colors']['info'] ?? '#1d5a96',
            'background_color' => $brand['tokens']['body_bg'] ?? '#fafafa',
            'text_color' => $brand['tokens']['body_text'] ?? '#18181b',
            'border_radius' => $brand['tokens']['section_radius'] ?? '1rem',
            'logo_path' => null,
            'favicon_path' => null,
            'custom_css' => null,
            'design_notes' => null,
            'updated_by' => null,
        ];
    }

    public function effectiveUiLocale(): string
    {
        $raw = $this->getAttribute('ui_locale');
        if (is_string($raw) && in_array($raw, ['de', 'en'], true)) {
            return $raw;
        }

        return 'de';
    }

    /**
     * Für spätere Theme-Anbindung: Werte mit Konfig-Fallbacks mergen.
     *
     * @return array<string, string|null>
     */
    public function resolvedPalette(): array
    {
        $defaults = static::defaultAttributes();

        return [
            'app_name' => filled($this->app_name) ? (string) $this->app_name : (string) ($defaults['app_name'] ?? config('app.name')),
            'primary_color' => (string) ($this->primary_color ?: $defaults['primary_color']),
            'secondary_color' => (string) ($this->secondary_color ?: $defaults['secondary_color']),
            'accent_color' => (string) ($this->accent_color ?: $defaults['accent_color']),
            'background_color' => (string) ($this->background_color ?: $defaults['background_color']),
            'text_color' => (string) ($this->text_color ?: $defaults['text_color']),
            'border_radius' => (string) ($this->border_radius ?: $defaults['border_radius']),
        ];
    }

    public static function resolveConfiguredPublicLogoUrl(): ?string
    {
        $path = config('filament-brand.logo.path');
        if (! is_string($path) || trim($path) === '') {
            return null;
        }
        if (! is_file(public_path($path))) {
            return null;
        }

        return asset($path);
    }

    /**
     * FileUpload / Livewire can leave a single path as string, or temporarily as a one-element array.
     *
     * @param  'logo_path'|'favicon_path'  $attribute
     */
    public function publicDiskRelativePath(string $attribute): ?string
    {
        $raw = $this->getAttribute($attribute);

        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_array($raw)) {
            $first = $raw[0] ?? null;

            return is_string($first) && $first !== '' ? $first : null;
        }

        if (is_string($raw)) {
            $trim = trim($raw);
            if ($trim === '') {
                return null;
            }
            if (str_starts_with($trim, '[')) {
                $decoded = json_decode($trim, true);
                if (is_array($decoded) && isset($decoded[0]) && is_string($decoded[0]) && $decoded[0] !== '') {
                    return $decoded[0];
                }
            }

            return $trim;
        }

        return null;
    }

    public function resolvedLogoPublicUrl(): ?string
    {
        $relative = $this->publicDiskRelativePath('logo_path');
        if ($relative !== null && Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->url($relative);
        }

        return static::resolveConfiguredPublicLogoUrl();
    }

    public function resolvedFaviconPublicUrl(): ?string
    {
        $relative = $this->publicDiskRelativePath('favicon_path');
        if ($relative !== null && Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->url($relative);
        }

        return null;
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
