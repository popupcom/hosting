<?php

namespace App\Providers;

use App\Models\DesignSetting;
use App\Models\Project;
use App\Models\ProjectService;
use App\Observers\ProjectObserver;
use App\Observers\ProjectServiceObserver;
use Filament\Events\ServingFilament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProjectService::observe(ProjectServiceObserver::class);
        Project::observe(ProjectObserver::class);

        Event::listen(ServingFilament::class, function (): void {
            try {
                if (! Schema::hasTable('design_settings')) {
                    return;
                }
                App::setLocale(DesignSetting::current()->effectiveUiLocale());
            } catch (Throwable) {
                //
            }
        });

        foreach (
            [
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_BEFORE,
                PanelsRenderHook::AUTH_PASSWORD_RESET_RESET_FORM_BEFORE,
            ] as $hook
        ) {
            FilamentView::registerRenderHook(
                $hook,
                fn (): string => view('filament.brand.auth-intro')->render(),
                scopes: 'admin',
            );
        }

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => view('filament.brand.design-tokens')->render(),
            scopes: 'admin',
        );

        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(120)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        RateLimiter::for('webhooks', function (Request $request): Limit {
            return Limit::perMinute(60)->by((string) $request->ip());
        });
    }
}
