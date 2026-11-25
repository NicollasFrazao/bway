<?php

declare(strict_types=1);

use App\Application\FloorPlan\MapFloorPlan;
use App\Application\FloorPlan\StoreGridWriter;
use App\Application\Routing\FindRouteBetweenSectors;
use App\Application\Routing\RouteLengthsBetweenSectors;
use App\Application\Visit\MapVisit;
use App\Application\Visit\PlanVisit;
use App\Application\Visit\VisitLeg;
use App\Application\Visit\VisitMap;
use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Shared\Name;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\Store\Assortment;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorKind;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Visit\NearestNeighbourOrdering;
use App\Domain\Visit\TwoOptOrdering;
use App\Domain\Visit\VisitPlanner;
use Tests\Support\AisleShop;
use Tests\Support\Domain\ImmediateTransaction;
use Tests\Support\Domain\InMemoryAssortmentRepository;
use Tests\Support\Domain\InMemoryRouteCache;
use Tests\Support\Domain\InMemoryShoppingListRepository;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\TextFloorPlanDecoder;

/**
 * The map of a store's visit for user 1 with the list given; the plan is not mapped when it is null.
 */
function visitMapOf(Store $store, ?string $floorPlan, Assortment $assortment, ShoppingList $list): VisitMap
{
    $stores = new InMemoryStoreRepository($store);
    $grids = new InMemoryStoreGridRepository;
    $routes = new InMemoryRouteCache;
    $lists = new InMemoryShoppingListRepository;
    $lists->save($list);

    if ($floorPlan !== null) {
        new MapFloorPlan($stores, new FloorPlanMapper(new LegendColorClassifier, new TextFloorPlanDecoder), new StoreGridWriter($grids, $routes, new ImmediateTransaction))
            ->handle($store->id, $floorPlan);
    }

    $find = new FindRouteBetweenSectors($stores, $grids, $routes, new AStarPathfinder(new ManhattanDistance));
    $plan = new PlanVisit(
        $stores,
        new InMemoryAssortmentRepository($assortment),
        $lists,
        new VisitPlanner(new TwoOptOrdering(new NearestNeighbourOrdering), new RouteLengthsBetweenSectors($find)),
    );

    return new MapVisit($plan, $find, $stores, $grids)->handle(new UserId(1), $store->id);
}

/**
 * The map of the aisle shop's visit for the named products.
 */
function aisleMap(bool $mapped, string ...$products): VisitMap
{
    return visitMapOf(AisleShop::store(), $mapped ? AisleShop::floorPlan() : null, AisleShop::assortment(), AisleShop::listOf(...$products));
}

it('draws one walk per stop, from the entrance, with the size of the grid', function (): void {
    $map = aisleMap(true, 'Apples', 'Milk', 'Bread');

    expect($map->size->width)->toBe(3)
        ->and($map->size->height)->toBe(9)
        ->and(array_map(static fn (VisitLeg $leg): string => $leg->from->name->value.' > '.$leg->to->name->value, $map->legs))
        ->toBe(['Entrance > Dairy', 'Dairy > Bakery', 'Bakery > Produce'])
        ->and(array_sum(array_map(static fn (VisitLeg $leg): int => $leg->length, $map->legs)))->toBe($map->plan->length)
        ->and($map->store->name->value)->toBe('Aisle shop');
});

it('keeps only where a walk starts, turns and ends', function (): void {
    $store = new Store(new StoreId(1), Name::from('Bend'), [
        new Sector(new SectorId(1), Name::from('Entrance'), new GridArea(new GridCoordinate(1, 1), 1, 1), SectorKind::Entrance),
        new Sector(new SectorId(2), Name::from('Bakery'), new GridArea(new GridCoordinate(3, 3), 1, 1), SectorKind::Sales),
    ], []);
    $plan = implode("\n", ['RRRRR', 'ROOOR', 'RRROR', 'RRROR', 'RRRRR']);
    $assortment = new Assortment(new StoreId(1), new SectorProduct(new SectorId(2), AisleShop::product('Bread')->id));

    $straight = (aisleMap(true, 'Apples')->legs[0] ?? throw new LogicException('No walk.'))->corners;
    $bend = (visitMapOf($store, $plan, $assortment, AisleShop::listOf('Bread'))->legs[0] ?? throw new LogicException('No walk.'))->corners;

    expect($straight)->toEqual([new GridCoordinate(1, 3), new GridCoordinate(1, 7)])
        ->and($bend)->toEqual([new GridCoordinate(1, 1), new GridCoordinate(3, 1), new GridCoordinate(3, 3)]);
});

it('draws no walk for a list the store does not sell', function (): void {
    expect(aisleMap(true, 'Batteries')->legs)->toBe([]);
});

it('cannot map the visit of a store whose plan is not mapped', function (): void {
    aisleMap(false, 'Apples');
})->throws(GridNotMapped::class);
