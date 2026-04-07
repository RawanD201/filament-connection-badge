<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    | Master switch to enable or disable the connection badge globally.
    | Set to false to completely remove the badge (and its render hook
    | and heartbeat route) from your Filament panels.
    */

    'enabled' => env('FILAMENT_CONNECTION_BADGE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Render Hook
    |--------------------------------------------------------------------------
    | Filament panel render hook name where the badge is injected. Defaults
    | to the user menu slot before the avatar. Use any PanelsRenderHook
    | string (e.g. panels::topbar.end) or set FILAMENT_CONNECTION_BADGE_RENDER_HOOK.
    */

    'render_hook' => env(
        'FILAMENT_CONNECTION_BADGE_RENDER_HOOK',
        'panels::user-menu.before'
    ),

    /*
    |--------------------------------------------------------------------------
    | Permission
    |--------------------------------------------------------------------------
    | Optional Laravel permission / Gate ability name. When set, the badge
    | is only shown and the ping endpoint only responds for users who pass
    | can(). Leave null to allow everyone who can reach the panel (subject
    | to route middleware). Set via FILAMENT_CONNECTION_BADGE_PERMISSION.
    */

    'permission' => env('FILAMENT_CONNECTION_BADGE_PERMISSION'),

    /*
    |--------------------------------------------------------------------------
    | Show Text Label
    |--------------------------------------------------------------------------
    | Show a text label next to the signal icon (e.g. "Full", "Medium").
    | The badge is always rendered in the Filament topbar, right before
    | the user menu.
    */

    'show_label' => true,

    /*
    |--------------------------------------------------------------------------
    | Offline Overlay
    |--------------------------------------------------------------------------
    | When true, a full-screen blocking overlay is shown when the browser
    | detects that the connection has been lost.
    */

    'show_overlay' => true,

    /*
    |--------------------------------------------------------------------------
    | Heartbeat Route
    |--------------------------------------------------------------------------
    | The package registers its own lightweight JSON heartbeat endpoint
    | which the badge in the browser pings to measure real round-trip
    | latency to your Laravel application — proving that PHP, the router
    | and the middleware stack are all responding, not just the static
    | file server.
    |
    |   prefix      → URI prefix for the route (default `_filament-connection-badge`)
    |   middleware  → Middleware applied to the route. Defaults to `web`
    |                 so the session is started; add `auth` to restrict
    |                 the endpoint to logged-in users only.
    |   throttle    → Optional Laravel throttle signature (e.g. `60,1` for
    |                 60 requests per minute). Appended as `throttle:{value}`.
    |                 Null disables throttling.
    */

    'route' => [
        'prefix' => '_filament-connection-badge',
        'middleware' => ['web'],
        'throttle' => env('FILAMENT_CONNECTION_BADGE_THROTTLE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ping URL Override
    |--------------------------------------------------------------------------
    | By default the badge pings the package's own heartbeat route
    | (see `route` above). Set this to a fully-qualified URL only if you
    | want to monitor a different endpoint instead.
    */

    'ping_url' => env('FILAMENT_CONNECTION_BADGE_PING_URL'),

    /*
    |--------------------------------------------------------------------------
    | Ping Interval
    |--------------------------------------------------------------------------
    | How often to re-check, in milliseconds. A lower value gives a more
    | responsive graph at the cost of slightly more network traffic.
    */

    'ping_interval' => 5000,

    /*
    |--------------------------------------------------------------------------
    | Latency Thresholds (ms)
    |--------------------------------------------------------------------------
    | The latency (round-trip time in milliseconds) at which the badge
    | switches quality levels. A measured ping:
    |
    |   - below `full`    → shown as "Full"
    |   - below `medium`  → shown as "Medium"
    |   - otherwise       → shown as "Low"
    */

    'thresholds' => [
        'full' => 200,
        'medium' => 600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ping History Size
    |--------------------------------------------------------------------------
    | Number of ping samples kept in memory for the tooltip graph and the
    | average/packet-loss calculations.
    */

    'max_samples' => 30,

];
