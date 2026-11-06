<?php

declare(strict_types=1);

use App\Application\FloorPlan\MapFloorPlan;
use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Grid\Grid;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\Route;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\LayoutOutsideGrid;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use Tests\Support\CornerShop;
use Tests\Support\Domain\InMemoryRouteCache;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\GridDrawing;
use Tests\Support\TextFloorPlanDecoder;

/**
 * The use case over the corner shop, with the grids and routes it keeps.
 *
 * @return array{MapFloorPlan, InMemoryStoreGridRepository, InMemoryRouteCache}
 */
function floorPlanMapping(): array
{
    $grids = new InMemoryStoreGridRepository;
    $routes = new InMemoryRouteCache;
    $mapping = new MapFloorPlan(
        new InMemoryStoreRepository(CornerShop::store()),
        new FloorPlanMapper(new LegendColorClassifier, new TextFloorPlanDecoder),
        $grids,
        $routes,
    );

    return [$mapping, $grids, $routes];
}

it('maps the floor plan and keeps the grid under the store id', function (): void {
    [$mapping, $grids] = floorPlanMapping();

    $grid = $mapping->handle(new StoreId(1), CornerShop::floorPlan());

    expect($grid)->toEqual(GridDrawing::grid(['###', '#o#', '#o#', '#o#', '#P#', '#o#', '###']))
        ->and($grids->forStore(new StoreId(1)))->toBe($grid);
});

it('forgets the routes found on the earlier grid', function (): void {
    [$mapping, , $routes] = floorPlanMapping();
    $routes->remember(new StoreId(1), new SectorId(1), new SectorId(2), new Route([new GridCoordinate(1, 1)]));

    $mapping->handle(new StoreId(1), CornerShop::floorPlan());

    expect($routes->find(new StoreId(1), new SectorId(1), new SectorId(2)))->toBeNull();
});

it('refuses a plan too small for the store and keeps nothing', function (): void {
    [$mapping, $grids] = floorPlanMapping();
    $tooSmall = implode("\n", ['RRR', 'ROR', 'ROR', 'ROR', 'RRR']);

    expect(fn (): Grid => $mapping->handle(new StoreId(1), $tooSmall))
        ->toThrow(LayoutOutsideGrid::class, 'In store 1, sector 3 reaches beyond the 3 x 5 grid of its floor plan.')
        ->and(fn (): Grid => $grids->forStore(new StoreId(1)))
        ->toThrow(GridNotMapped::class, 'The floor plan of store 1 has not been mapped yet.');
});

it('refuses a store that does not exist', function (): void {
    [$mapping] = floorPlanMapping();

    expect(fn (): Grid => $mapping->handle(new StoreId(9), CornerShop::floorPlan()))
        ->toThrow(StoreNotFound::class);
});
