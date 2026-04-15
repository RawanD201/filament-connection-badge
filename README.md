# Filament Connection Badge

<img src="docs/art/banner.jpeg" alt="Filament Connection Badge" class="filament-hidden">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rawand201/filament-connection-badge.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-connection-badge)
[![Total Downloads](https://img.shields.io/packagist/dt/rawand201/filament-connection-badge.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-connection-badge)
[![License](https://img.shields.io/github/license/RawanD201/filament-connection-badge.svg?style=flat-square)](LICENSE.md)
[![CI](https://github.com/RawanD201/filament-connection-badge/actions/workflows/ci.yml/badge.svg)](https://github.com/RawanD201/filament-connection-badge/actions/workflows/ci.yml)

A drop-in **connection status badge** for Filament panels — shows a live signal-bars icon in the topbar next to the user avatar, a live ping graph on hover, and a full-screen overlay when the connection drops.

Works with **Filament v4 and v5**, uses Filament's design tokens so it follows your panel's theme and dark mode automatically, and fully supports **RTL** and multi-language UIs.

---

## Requirements

| | Version |
|---|---|
| PHP | ^8.2 (8.2 / 8.3 / 8.4) |
| Laravel | 10.x / 11.x / 12.x / 13.x |
| Filament | ^4.0 \| ^5.0 |

---

## Installation

```bash
composer require rawand201/filament-connection-badge
```

The service provider is auto-discovered, so no manual registration is needed.

### Publish assets

```bash
php artisan filament:assets
```

### Publish the config (optional)

```bash
php artisan vendor:publish --tag="filament-connection-badge-config"
```

### Publish translations (optional)

```bash
php artisan vendor:publish --tag="filament-connection-badge-translations"
```

That's it — the badge is now rendered in the topbar of every Filament panel.

---

## Configuration

The published `config/filament-connection-badge.php`:

```php
return [

    'enabled' => env('FILAMENT_CONNECTION_BADGE_ENABLED', true),

    // Filament panel hook name (default: before the user menu). See Filament\View\PanelsRenderHook.
    'render_hook' => env(
        'FILAMENT_CONNECTION_BADGE_RENDER_HOOK',
        'panels::user-menu.before'
    ),

    // Optional Gate / policy ability — badge and ping require auth()->user()->can(...) when set
    'permission' => env('FILAMENT_CONNECTION_BADGE_PERMISSION'),

    'show_label' => true,

    'show_overlay' => true,

    'route' => [
        'prefix' => '_filament-connection-badge',
        'middleware' => ['web'],
        'throttle' => env('FILAMENT_CONNECTION_BADGE_THROTTLE'), // e.g. '60,1'; null = no throttle
    ],

    'ping_url' => env('FILAMENT_CONNECTION_BADGE_PING_URL'),

    'ping_interval' => 5000,

    'thresholds' => [
        'full' => 200,
        'medium' => 600,
    ],

    'max_samples' => 30,

];
```

### Environment variables

```dotenv
FILAMENT_CONNECTION_BADGE_ENABLED=true
FILAMENT_CONNECTION_BADGE_RENDER_HOOK=panels::topbar.end
FILAMENT_CONNECTION_BADGE_PERMISSION=viewConnectionBadge
FILAMENT_CONNECTION_BADGE_PING_URL=/favicon.ico
FILAMENT_CONNECTION_BADGE_THROTTLE=60,1
```

### Disabling the badge for a specific environment

```dotenv
FILAMENT_CONNECTION_BADGE_ENABLED=false
```

---

## Features

- **Live signal bars** in the topbar — full / medium / low / offline, colored with Filament's `--success-*`, `--warning-*`, `--danger-*` tokens
- **Real heartbeat endpoint** — the package registers its own JSON `/_filament-connection-badge/ping` route, so the badge actually proves your **Laravel application** is responding (PHP + router + middleware), not just the static file server
- **Discord-style hover tooltip** with:
  - Live sparkline of the last N ping samples
  - Average ping, last ping, packet-loss rate
  - Host indicator
- **Full-screen offline overlay** that auto-dismisses when the connection comes back
- **Filament v4 & v5** — one package, single code path
- **Dark mode** — all colors follow the panel theme via CSS variables
- **RTL support** — built with CSS logical properties; numbers and the ping graph stay LTR (`unicode-bidi: isolate`) so measurements stay readable in RTL UIs
- **i18n** — ships with English, Arabic, and Kurdish Sorani translations
- **Configurable** — enable/disable, render hook, optional `can()` permission, optional heartbeat throttling, latency thresholds, route prefix & middleware, ping interval, history size, label visibility, overlay on/off
- **Zero build step** — no Vite, no npm; assets are registered via `FilamentAsset`

---

## How it works

- On every Filament page, a tiny Alpine.js component is rendered via the configured render hook (default `panels::user-menu.before`, same as `PanelsRenderHook::USER_MENU_BEFORE`).
- The package registers its own heartbeat route — a named GET endpoint at `route('filament-connection-badge.ping')` (default URI `/_filament-connection-badge/ping`) that returns a small JSON payload `{ ok: true, ts: ... }` with `Cache-Control: no-store`.
- The Alpine component pings that endpoint every `ping_interval` ms and measures the round-trip time with `performance.now()`. Because the request goes through the Laravel router and middleware stack, a successful ping proves your **app** is responding — not just nginx serving a static file.
- The measured latency is mapped to a quality level using `thresholds`.
- A rolling buffer of the last `max_samples` pings is kept in memory. Failed pings count toward the packet-loss rate and visually break the sparkline.
- `navigator.onLine` and the `online` / `offline` events are used as an immediate hint; a confirmation ping always follows.
- When the browser reports offline (or a ping fails), the badge flips to "Offline" and the optional overlay is shown until the next successful ping.

Nothing is persisted server-side — the route is stateless and the rolling sample buffer lives in the browser.

### Securing the heartbeat route

By default the route uses the `web` middleware so sessions and cookies work, but it's publicly reachable. You can lock it down in two ways (combinable):

**Middleware** — require an authenticated session:

```php
'route' => [
    'prefix' => '_filament-connection-badge',
    'middleware' => ['web', 'auth'],
],
```

Or Filament's panel auth middleware:

```php
'middleware' => ['web', \Filament\Http\Middleware\Authenticate::class],
```

**Permission** — set `permission` (or `FILAMENT_CONNECTION_BADGE_PERMISSION`) to a Laravel Gate ability or policy method name. When set, the badge is hidden and the ping endpoint returns `403` unless `auth()->user()->can($permission)`. Register the ability in a service provider (e.g. `Gate::define('viewConnectionBadge', ...)`).

You can also set `route.throttle` in config (or `FILAMENT_CONNECTION_BADGE_THROTTLE`, e.g. `60,1`) to append Laravel’s `throttle:{max},{decayMinutes}` middleware to the heartbeat route only.

---

## Localization

To add another language, publish the translations and copy any of the files:

```bash
php artisan vendor:publish --tag="filament-connection-badge-translations"
```
---

## Customization

### Override the view

```bash
php artisan vendor:publish --tag="filament-connection-badge-views"
```

### Override the styles

You can either:

- Publish and edit the asset files, or
- Simply add your own CSS targeting the `.fcb-*` classes — every color is declared with `var(--gray-*)` / `var(--success-*)` / etc. so they automatically track your panel's theme.


```bash
composer sync-assets
```

---

## Testing

```bash
composer test
composer analyse   # PHPStan (src/)
```


## Changelog

See [CHANGELOG](CHANGELOG.md).


## Credits

- [RawanD201](https://github.com/RawanD201)


## License

The MIT License (MIT). See [LICENSE](LICENSE.md).
