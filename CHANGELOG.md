# Changelog

All notable changes to `filament-connection-badge` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-04-14

### Added

- Topbar connection badge with signal levels: full, medium, low, offline (Filament `--success-*` / `--warning-*` / `--danger-*` tokens).
- Heartbeat route: `/_filament-connection-badge/ping` (named `filament-connection-badge.ping`) — JSON ping so latency reflects the Laravel app (router + middleware), not only static files.
- Hover tooltip with rolling ping sparkline, average / last ping, packet loss, host.
- Full-screen offline overlay until the connection recovers.
- **Config:** `enabled`, `render_hook` (default `panels::user-menu.before`), `permission` (optional `can()` for badge + ping), `show_label`, `show_overlay`, `route.prefix`, `route.middleware`, `route.throttle` / `FILAMENT_CONNECTION_BADGE_THROTTLE`, `ping_url`, `ping_interval`, `thresholds`, `max_samples`.
- Filament v4 and v5 (render hook registration).
- RTL-friendly CSS (logical properties); numeric/ping graph stays LTR where needed.
- Translations: English (`en`), Arabic (`ar`), Kurdish Sorani (`ckb`).
- Frontend sources: `resources/js` and `resources/css`; `composer sync-assets` mirrors into `resources/dist` for optional `vendor:publish` assets.
- PHPStan (level 6) + Larastan on `src/`, `composer analyse`, CI (Pint + PHPStan + Pest).
- Tests for config defaults, heartbeat JSON, ping forbidden when `permission` is set for guests, and disabled package (no ping route).

### Changed

- When `enabled` is `false`, the service provider skips Filament assets, routes, and render hooks.
