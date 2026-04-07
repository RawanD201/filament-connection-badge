<?php

declare(strict_types=1);

namespace Rawand\FilamentConnectionBadge\Tests;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;

class DisabledConnectionBadgeTest extends DisabledPackageTestCase
{
    #[Test]
    public function it_does_not_register_the_ping_route_when_the_package_is_disabled(): void
    {
        $this->assertFalse(Route::has('filament-connection-badge.ping'));
    }
}
