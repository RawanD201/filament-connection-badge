<?php

declare(strict_types=1);

use Rawand\FilamentConnectionBadge\FilamentConnectionBadgeServiceProvider;

it('loads the package config with expected defaults', function () {
    expect(config('filament-connection-badge'))->toBeArray()
        ->and(config('filament-connection-badge.enabled'))->toBeTrue()
        ->and(config('filament-connection-badge.render_hook'))
        ->toBe('panels::user-menu.before')
        ->and(config('filament-connection-badge.permission'))->toBeNull()
        ->and(config('filament-connection-badge.route.throttle'))->toBeNull()
        ->and(config('filament-connection-badge.show_label'))->toBeTrue()
        ->and(config('filament-connection-badge.show_overlay'))->toBeTrue()
        ->and(config('filament-connection-badge.ping_interval'))->toBeInt()
        ->and(config('filament-connection-badge.max_samples'))->toBe(30);
});

it('defines both latency thresholds', function () {
    $thresholds = config('filament-connection-badge.thresholds');

    expect($thresholds)->toBeArray()
        ->and($thresholds['full'])->toBeInt()->toBeLessThan($thresholds['medium'])
        ->and($thresholds['medium'])->toBeInt();
});

it('registers the service provider', function () {
    expect(app()->getProvider(FilamentConnectionBadgeServiceProvider::class))
        ->not->toBeNull();
});

it('registers the filament-connection-badge translation namespace', function () {
    expect(trans('filament-connection-badge::messages.full'))
        ->toBeString()
        ->not->toBe('filament-connection-badge::messages.full');
});

it('registers the filament-connection-badge view namespace', function () {
    expect(view()->exists('filament-connection-badge::badge'))->toBeTrue();
});

it('returns heartbeat json from the ping route', function () {
    $response = $this->get('/_filament-connection-badge/ping');

    $response->assertOk();
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    $response->assertJsonStructure(['ok', 'ts'])->assertJson(['ok' => true]);
});

it('forbids ping when permission is set and the user is a guest', function () {
    config(['filament-connection-badge.permission' => 'filament-connection-badge-ping']);

    $this
        ->get('/_filament-connection-badge/ping')
        ->assertForbidden();
});
