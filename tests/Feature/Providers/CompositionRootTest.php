<?php

declare(strict_types=1);

use App\Application\Routing\RouteLengthsBetweenSectors;
use App\Domain\Catalog\ProductCatalog;
use App\Domain\FloorPlan\ColorClassifier;
use App\Domain\FloorPlan\FloorPlanDecoder;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\Heuristic;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Routing\Pathfinder;
use App\Domain\Routing\RouteCache;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreRepository;
use App\Domain\Visit\DistanceTable;
use App\Domain\Visit\RouteLengths;
use App\Domain\Visit\TwoOptOrdering;
use App\Domain\Visit\VisitOrderingStrategy;
use App\Infrastructure\FloorPlan\GdFloorPlanDecoder;
use App\Infrastructure\Persistence\EloquentAssortmentRepository;
use App\Infrastructure\Persistence\EloquentProductCatalog;
use App\Infrastructure\Persistence\EloquentRouteCache;
use App\Infrastructure\Persistence\EloquentShoppingListRepository;
use App\Infrastructure\Persistence\EloquentStoreGridRepository;
use App\Infrastructure\Persistence\EloquentStoreRepository;

it('binds every port to its adapter', function (string $port, string $adapter): void {
    expect(get_debug_type(app($port)))->toBe($adapter);
})->with([
    'color classifier' => [ColorClassifier::class, LegendColorClassifier::class],
    'floor plan decoder' => [FloorPlanDecoder::class, GdFloorPlanDecoder::class],
    'heuristic' => [Heuristic::class, ManhattanDistance::class],
    'pathfinder' => [Pathfinder::class, AStarPathfinder::class],
    'product catalog' => [ProductCatalog::class, EloquentProductCatalog::class],
    'shopping list repository' => [ShoppingListRepository::class, EloquentShoppingListRepository::class],
    'store repository' => [StoreRepository::class, EloquentStoreRepository::class],
    'assortment repository' => [AssortmentRepository::class, EloquentAssortmentRepository::class],
    'store grid repository' => [StoreGridRepository::class, EloquentStoreGridRepository::class],
    'route cache' => [RouteCache::class, EloquentRouteCache::class],
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
