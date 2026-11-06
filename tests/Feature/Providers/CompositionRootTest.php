<?php

declare(strict_types=1);

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

it('binds every port of B2 to its adapter', function (string $port, string $adapter): void {
    expect(get_debug_type(app($port)))->toBe($adapter);
})->with([
    'color classifier' => [ColorClassifier::class, LegendColorClassifier::class],
    'floor plan decoder' => [FloorPlanDecoder::class, GdFloorPlanDecoder::class],
    'heuristic' => [Heuristic::class, ManhattanDistance::class],
    'pathfinder' => [Pathfinder::class, AStarPathfinder::class],
    'store grid repository' => [StoreGridRepository::class, FilesystemStoreGridRepository::class],
    'route cache' => [RouteCache::class, FilesystemRouteCache::class],
]);
