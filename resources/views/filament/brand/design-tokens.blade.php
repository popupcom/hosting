@php
    use App\Models\DesignSetting;
    use Illuminate\Support\Facades\Schema;
    use Throwable;

    /** @var array<string, scalar> $tokens */
    $tokens = config('filament-brand.tokens', []);
    /** @var array<string, string> $notificationTokens */
    $notificationTokens = \App\Support\NotificationStyleTokens::defaults();
    $customCss = null;

    try {
        if (Schema::hasTable('design_settings')) {
            $setting = DesignSetting::current();
            $palette = $setting->resolvedPalette();
            $notificationTokens = $setting->resolvedNotificationTokens();

            if (filled($palette['background_color'] ?? null)) {
                $bg = (string) $palette['background_color'];
                $tokens['body_bg'] = $bg;
                $tokens['login_gradient_from'] = $bg;
                $tokens['login_gradient_to'] = $bg;
            }

            if (filled($palette['text_color'] ?? null)) {
                $tokens['body_text'] = (string) $palette['text_color'];
            }

            if (filled($palette['border_radius'] ?? null)) {
                $tokens['section_radius'] = (string) $palette['border_radius'];
            }

            if (filled($setting->custom_css)) {
                $customCss = (string) $setting->custom_css;
            }
        }
    } catch (Throwable) {
        //
    }
@endphp
<style id="popup-filament-design-tokens">
    :root {
        @foreach ($tokens as $name => $value)
            --popup-{{ \Illuminate\Support\Str::kebab((string) $name) }}: {{ e((string) $value) }};
        @endforeach
        @foreach ($notificationTokens as $name => $value)
            --popup-notifications-{{ \Illuminate\Support\Str::kebab((string) $name) }}: {{ e((string) $value) }};
        @endforeach
    }
</style>
@if (filled($customCss))
<style id="popup-filament-custom-css">
{!! $customCss !!}
</style>
@endif
