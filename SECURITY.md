# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

Only the latest released version is actively maintained. Please upgrade before reporting an issue.

## Package Surface

This package adds one HTTP endpoint to a Filament panel: a heartbeat route (default
`/_filament-connection-badge/ping`) that returns `{ ok: true, ts: ... }`. Nothing is
persisted server-side — the route is stateless, and the ping history used for the
sparkline lives only in the browser.

By default the route uses the `web` middleware, so it is reachable by anyone who can
reach the panel host, authenticated or not. If that's not acceptable for your
deployment, see the "Securing the heartbeat route" section in the [README](README.md)
for restricting it with middleware (`auth`, Filament's panel auth), a `Gate`
permission, and/or request throttling.

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

Instead, report it privately using one of:

- [GitHub Security Advisories](https://github.com/RawanD201/filament-connection-badge/security/advisories/new) (preferred)
- Email: **rawandrasool@proton.me**

Please include:

- A description of the vulnerability and its potential impact
- Steps to reproduce, or a minimal proof of concept
- The package version and Filament/Laravel versions involved

We will acknowledge your report within a few days and aim to release a fix as soon
as reasonably possible. Credit will be given in the changelog if desired.
