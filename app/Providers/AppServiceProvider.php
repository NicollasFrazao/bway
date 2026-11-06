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
use App\Domain\Routing\RouteCache;
use App\Domain\Store\StoreGridRepository;
use App\Infrastructure\FloorPlan\GdFloorPlanDecoder;
use App\Infrastructure\Storage\FilesystemRouteCache;
use App\Infrastructure\Storage\FilesystemStoreGridRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of the floor plans and the routing: binds their ports to their adapters.
 *
 * The providers in app/Providers are the only classes that may depend on every layer; the visit
 * ports are bound in VisitServiceProvider. StoreRepository and the other repositories of B1 have no
 * production adapter until issue #7.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Binds the floor-plan and routing ports. Grids and routes are kept on the local disk
     * (storage/app/private), resolved when the port is, so a test's Storage::fake() takes its place.
     */
    public function register(): void
    {
        $this->app->bind(ColorClassifier::class, LegendColorClassifier::class);
        $this->app->bind(FloorPlanDecoder::class, GdFloorPlanDecoder::class);
        $this->app->bind(Heuristic::class, ManhattanDistance::class);
        $this->app->bind(Pathfinder::class, AStarPathfinder::class);
        $this->app->bind(
            StoreGridRepository::class,
            static fn (): StoreGridRepository => new FilesystemStoreGridRepository(Storage::disk('local')),
        );
        $this->app->bind(
            RouteCache::class,
            static fn (): RouteCache => new FilesystemRouteCache(Storage::disk('local')),
        );
    }
}
