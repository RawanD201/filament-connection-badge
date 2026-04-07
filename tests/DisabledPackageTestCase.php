<?php

declare(strict_types=1);

namespace Rawand\FilamentConnectionBadge\Tests;

use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Rawand\FilamentConnectionBadge\FilamentConnectionBadgeServiceProvider;

class DisabledPackageTestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentConnectionBadgeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('filament-connection-badge.enabled', false);
    }
}
