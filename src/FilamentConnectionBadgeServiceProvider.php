<?php

declare(strict_types=1);

namespace Rawand\FilamentConnectionBadge;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Route;
use Rawand\FilamentConnectionBadge\Http\Controllers\PingController;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentConnectionBadgeServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-connection-badge';

    public static string $viewNamespace = 'filament-connection-badge';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasTranslations()
            ->hasAssets();
    }

    public function packageBooted(): void
    {
        if (! config('filament-connection-badge.enabled', true)) {
            return;
        }

        $this->registerFilamentAssets();
        $this->registerRoutes();
        $this->registerRenderHooks();
    }

    protected function registerFilamentAssets(): void
    {
        FilamentAsset::register([
            Css::make(
                'filament-connection-badge-styles',
                __DIR__.'/../resources/css/filament-connection-badge.css'
            ),
            Js::make(
                'filament-connection-badge-scripts',
                __DIR__.'/../resources/js/filament-connection-badge.js'
            ),
        ], package: 'rawand201/filament-connection-badge');
    }

    protected function registerRoutes(): void
    {
        Route::middleware($this->heartbeatRouteMiddleware())
            ->prefix(config('filament-connection-badge.route.prefix', '_filament-connection-badge'))
            ->group(function (): void {
                Route::get('ping', PingController::class)
                    ->name('filament-connection-badge.ping');
            });
    }

    /** @return list<string> */
    protected function heartbeatRouteMiddleware(): array
    {
        $stack = config('filament-connection-badge.route.middleware', ['web']);
        $throttle = config('filament-connection-badge.route.throttle');

        if (filled($throttle)) {
            $stack = [...$stack, 'throttle:'.$throttle];
        }

        return $stack;
    }

    protected function registerRenderHooks(): void
    {
        FilamentView::registerRenderHook(
            config(
                'filament-connection-badge.render_hook',
                'panels::user-menu.before'
            ),
            function (): string {
                $permission = config('filament-connection-badge.permission');

                if (filled($permission) && (! auth()->check() || ! auth()->user()->can($permission))) {
                    return '';
                }

                return view(static::$viewNamespace.'::badge')->render();
            },
        );
    }
}
