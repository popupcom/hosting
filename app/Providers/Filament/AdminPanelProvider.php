<?php

namespace App\Providers\Filament;

use App\Filament\Auth\ConsumeOneTimeLogin;
use App\Filament\Auth\Login;
use App\Filament\Auth\RequestOneTimeLogin;
use App\Filament\Pages\GestaltungSettingsPage;
use App\Filament\Pages\LocalizationSettingsPage;
use App\Models\DesignSetting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Throwable;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $brand = config('filament-brand.colors', []);

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(fn (): string => $this->designBrandName())
            ->brandLogo(fn (): ?string => $this->designBrandLogoUrl())
            ->brandLogoHeight(fn (): string => (string) config('filament-brand.logo.height', '2.75rem'))
            ->favicon(fn (): ?string => $this->designFaviconUrl())
            ->colors(fn (): array => $this->designFilamentColors($brand))
            ->login(Login::class)
            ->passwordReset(
                requestAction: RequestOneTimeLogin::class,
                resetAction: ConsumeOneTimeLogin::class,
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->userMenuItems([
                Action::make('localization')
                    ->label(new HtmlString(
                        '<span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Einstellungen</span>'
                        .'<span class="block text-sm font-semibold text-gray-950 dark:text-white">Sprache & Texte</span>',
                    ))
                    ->icon(Heroicon::OutlinedLanguage)
                    ->url(fn (): string => LocalizationSettingsPage::getUrl())
                    ->sort(490)
                    ->visible(function (): bool {
                        $user = Filament::auth()->user();

                        return $user instanceof User && $user->isAdmin();
                    }),
                Action::make('gestaltung')
                    ->label(new HtmlString(
                        '<span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Einstellungen</span>'
                        .'<span class="block text-sm font-semibold text-gray-950 dark:text-white">Gestaltung</span>',
                    ))
                    ->icon(Heroicon::OutlinedSwatch)
                    ->url(fn (): string => GestaltungSettingsPage::getUrl())
                    ->sort(500)
                    ->visible(function (): bool {
                        $user = Filament::auth()->user();

                        return $user instanceof User && $user->isAdmin();
                    }),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if (
            file_exists(public_path('build/manifest.json'))
            || file_exists(public_path('hot'))
        ) {
            $panel->viteTheme('resources/css/filament/admin/theme.css');
        }

        return $panel;
    }

    /**
     * @return array<string, string>
     */
    private function designResolvedPalette(): array
    {
        try {
            if (! Schema::hasTable('design_settings')) {
                return [];
            }

            return DesignSetting::current()->resolvedPalette();
        } catch (Throwable) {
            return [];
        }
    }

    private function designBrandName(): string
    {
        $palette = $this->designResolvedPalette();
        $name = $palette['app_name'] ?? null;

        return filled($name) ? (string) $name : (string) config('app.name');
    }

    private function designBrandLogoUrl(): ?string
    {
        try {
            if (! Schema::hasTable('design_settings')) {
                return DesignSetting::resolveConfiguredPublicLogoUrl();
            }

            return DesignSetting::current()->resolvedLogoPublicUrl();
        } catch (Throwable) {
            return DesignSetting::resolveConfiguredPublicLogoUrl();
        }
    }

    private function designFaviconUrl(): ?string
    {
        try {
            if (! Schema::hasTable('design_settings')) {
                return null;
            }

            return DesignSetting::current()->resolvedFaviconPublicUrl();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $brand
     * @return array<string, Color>
     */
    private function designFilamentColors(array $brand): array
    {
        $palette = $this->designResolvedPalette();
        $primary = (string) ($palette['primary_color'] ?? $brand['primary'] ?? '#d6002a');
        $info = (string) ($palette['accent_color'] ?? $brand['info'] ?? '#1d5a96');

        return [
            'primary' => Color::hex($primary),
            'success' => Color::hex((string) ($brand['success'] ?? '#0d6e4d')),
            'warning' => Color::hex((string) ($brand['warning'] ?? '#b45309')),
            'danger' => Color::hex((string) ($brand['danger'] ?? '#b42318')),
            'info' => Color::hex($info),
            'gray' => Color::Zinc,
        ];
    }
}
