<?php

declare(strict_types=1);

use App\Application\FloorPlan\MapFloorPlan;
use App\Application\Routing\FindRouteBetweenSectors;
use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use Tests\Support\CornerShop;
use Tests\Support\Domain\InMemoryRouteCache;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\TextFloorPlanDecoder;

/**
 * The use case over the corner shop, with the route cache it fills; the shop's plan is mapped first
 * unless the test says otherwise.
 *
 * @return array{FindRouteBetweenSectors, InMemoryRouteCache}
 */
function cornerShopRouting(bool $mapped = true): array
{
    $stores = new InMemoryStoreRepository(CornerShop::store());
    $grids = new InMemoryStoreGridRepository;
    $routes = new InMemoryRouteCache;

    if ($mapped) {
        new MapFloorPlan($stores, new FloorPlanMapper(new LegendColorClassifier, new TextFloorPlanDecoder), $grids, $routes)
            ->handle(new StoreId(1), CornerShop::floorPlan());
    }

    return [new FindRouteBetweenSectors($stores, $grids, $routes, new AStarPathfinder(new ManhattanDistance)), $routes];
}

it('finds the route between the centres of two sectors and keeps it', function (): void {
    [$routing, $routes] = cornerShopRouting();

    $route = $routing->handle(new StoreId(1), new SectorId(1), new SectorId(2));

    expect($route->cells)->toEqual([new GridCoordinate(1, 1), new GridCoordinate(1, 2), new GridCoordinate(1, 3)])
        ->and($routes->find(new StoreId(1), new SectorId(1), new SectorId(2)))->toBe($route)
        ->and($routes->find(new StoreId(1), new SectorId(2), new SectorId(1)))->toBeNull();
});

it('serves a kept route without searching the grid again', function (): void {
    [$routing, $routes] = cornerShopRouting(mapped: false);
    $kept = new Route([new GridCoordinate(1, 1), new GridCoordinate(1, 2)]);
    $routes->remember(new StoreId(1), new SectorId(1), new SectorId(2), $kept);

    expect($routing->handle(new StoreId(1), new SectorId(1), new SectorId(2)))->toBe($kept);
});

it('reports a store whose floor plan has not been mapped', function (): void {
    [$routing] = cornerShopRouting(mapped: false);

    expect(fn (): Route => $routing->handle(new StoreId(1), new SectorId(1), new SectorId(2)))
        ->toThrow(GridNotMapped::class);
});

it('reports a sector the store does not have, before looking at the cache', function (SectorId $from, SectorId $to): void {
    [$routing] = cornerShopRouting();

    expect(fn (): Route => $routing->handle(new StoreId(1), $from, $to))
        ->toThrow(SectorNotInStore::class, 'Store 1 has no sector 9.');
})->with([
    'origin' => [new SectorId(9), new SectorId(1)],
    'destination' => [new SectorId(1), new SectorId(9)],
]);

it('reports two sectors no walkway joins, and keeps nothing', function (): void {
    [$routing, $routes] = cornerShopRouting();

    expect(fn (): Route => $routing->handle(new StoreId(1), new SectorId(1), new SectorId(3)))
        ->toThrow(RouteNotFound::class, 'No walkway leads from (1, 1) to (1, 5).')
        ->and($routes->find(new StoreId(1), new SectorId(1), new SectorId(3)))->toBeNull();
});
