<?php

declare(strict_types=1);

use App\Application\Routing\RouteLengthsBetweenSectors;
use App\Domain\FloorPlan\ColorClassifier;
use App\Domain\FloorPlan\FloorPlanDecoder;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\Heuristic;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Routing\Pathfinder;
use App\Domain\Routing\RouteCache;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreRepository;
use App\Domain\Visit\DistanceTable;
use App\Domain\Visit\RouteLengths;
use App\Domain\Visit\TwoOptOrdering;
use App\Domain\Visit\VisitOrderingStrategy;
use App\Infrastructure\FloorPlan\GdFloorPlanDecoder;
use App\Infrastructure\Storage\FilesystemRouteCache;
use App\Infrastructure\Storage\FilesystemStoreGridRepository;
use Tests\Support\Domain\InMemoryStoreRepository;

beforeEach(function (): void {
    // StoreRepository has no production adapter until #7; the use cases behind RouteLengths need one.
    app()->instance(StoreRepository::class, new InMemoryStoreRepository);
});

it('binds every port of B2 and B3 to its adapter', function (string $port, string $adapter): void {
    expect(get_debug_type(app($port)))->toBe($adapter);
})->with([
    'color classifier' => [ColorClassifier::class, LegendColorClassifier::class],
    'floor plan decoder' => [FloorPlanDecoder::class, GdFloorPlanDecoder::class],
    'heuristic' => [Heuristic::class, ManhattanDistance::class],
    'pathfinder' => [Pathfinder::class, AStarPathfinder::class],
    'store grid repository' => [StoreGridRepository::class, FilesystemStoreGridRepository::class],
    'route cache' => [RouteCache::class, FilesystemRouteCache::class],
    'route lengths' => [RouteLengths::class, RouteLengthsBetweenSectors::class],
    'visit ordering strategy' => [VisitOrderingStrategy::class, TwoOptOrdering::class],
]);

it('orders visits by nearest neighbour improved by 2-opt', function (): void {
    // Entrance 1 at 0 on a line, stops 2 at +1, 3 at -2, 4 at +4: nearest neighbour alone walks 10 steps.
    $positions = [1 => 0, 2 => 1, 3 => -2, 4 => 4];
    $lengths = array_map(static fn (int $a): array => array_map(static fn (int $b): int => abs($a - $b), $positions), $positions);
    $stops = [new SectorId(2), new SectorId(3), new SectorId(4)];

    $order = app(VisitOrderingStrategy::class)->order(new SectorId(1), $stops, new DistanceTable($lengths));

    expect(array_map(static fn (SectorId $id): int => $id->value, $order))->toBe([3, 2, 4]);
});
