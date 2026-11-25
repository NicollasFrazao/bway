<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Routing\RouteLengthsBetweenSectors;
use App\Domain\Visit\NearestNeighbourOrdering;
use App\Domain\Visit\RouteLengths;
use App\Domain\Visit\TwoOptOrdering;
use App\Domain\Visit\VisitOrderingStrategy;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of the visit: binds the ports of the visit planner to their implementations.
 *
 * Kept apart from AppServiceProvider so each provider stays within the coupling limit of PHPMD.
 */
final class VisitServiceProvider extends ServiceProvider
{
    /**
     * Binds the route lengths to the routes found and cached by B2, and orders visits by nearest
     * neighbour improved by 2-opt, which is never longer than nearest neighbour alone
     * (docs/adr/0004-visit-planning.md).
     */
    public function register(): void
    {
        $this->app->bind(RouteLengths::class, RouteLengthsBetweenSectors::class);
        $this->app->bind(
            VisitOrderingStrategy::class,
            static fn (): VisitOrderingStrategy => new TwoOptOrdering(new NearestNeighbourOrdering),
        );
    }
}
