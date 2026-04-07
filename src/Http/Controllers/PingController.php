<?php

declare(strict_types=1);

namespace Rawand\FilamentConnectionBadge\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PingController
{
    public function __invoke(Request $request): JsonResponse
    {
        $permission = config('filament-connection-badge.permission');

        if (filled($permission)) {
            abort_unless(
                $request->user() && $request->user()->can($permission),
                403
            );
        }

        return response()
            ->json([
                'ok' => true,
                'ts' => now()->getPreciseTimestamp(3),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
