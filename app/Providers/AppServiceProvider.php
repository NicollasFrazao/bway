<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\FloorPlan\ColorClassifier;
use App\Domain\FloorPlan\FloorPlanDecoder;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\Heuristic;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Routing\Pathfinder;
use App\Infrastructure\FloorPlan\GdFloorPlanDecoder;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of the floor plans and the routing: binds their ports to their implementations.
 *
 * The providers in app/Providers are the only classes that may depend on every layer; the
 * repositories (grids and routes included) are bound in PersistenceServiceProvider, the visit ports
 * in VisitServiceProvider.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Binds the floor-plan decoding and the route search.
     */
    public function register(): void
    {
        $this->app->bind(ColorClassifier::class, LegendColorClassifier::class);
        $this->app->bind(FloorPlanDecoder::class, GdFloorPlanDecoder::class);
        $this->app->bind(Heuristic::class, ManhattanDistance::class);
        $this->app->bind(Pathfinder::class, AStarPathfinder::class);
    }
}
