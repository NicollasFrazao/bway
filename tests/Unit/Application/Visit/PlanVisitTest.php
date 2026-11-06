<?php

declare(strict_types=1);

use App\Application\FloorPlan\MapFloorPlan;
use App\Application\Routing\FindRouteBetweenSectors;
use App\Application\Routing\RouteLengthsBetweenSectors;
use App\Application\Visit\PlanVisit;
use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Shared\UserId;
use App\Domain\Store\Assortment;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Visit\NearestNeighbourOrdering;
use App\Domain\Visit\TwoOptOrdering;
use App\Domain\Visit\VisitOrderingStrategy;
use App\Domain\Visit\VisitPlan;
use App\Domain\Visit\VisitPlanner;
use App\Domain\Visit\VisitStop;
use Tests\Support\AisleShop;
use Tests\Support\CornerShop;
use Tests\Support\Domain\InMemoryAssortmentRepository;
use Tests\Support\Domain\InMemoryRouteCache;
use Tests\Support\Domain\InMemoryShoppingListRepository;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\TextFloorPlanDecoder;

/**
 * The use case over one store drawn as text, with the route cache it fills and the lists it reads;
 * the routes come from A* on the mapped plan. The plan is not mapped when it is null.
 *
 * @return array{PlanVisit, InMemoryRouteCache, InMemoryShoppingListRepository}
 */
function visitPlanning(Store $store, Assortment $assortment, ?string $floorPlan, VisitOrderingStrategy $strategy): array
{
    $stores = new InMemoryStoreRepository($store);
    $grids = new InMemoryStoreGridRepository;
    $routes = new InMemoryRouteCache;
    $lists = new InMemoryShoppingListRepository;

    if ($floorPlan !== null) {
        new MapFloorPlan($stores, new FloorPlanMapper(new LegendColorClassifier, new TextFloorPlanDecoder), $grids, $routes)
            ->handle($store->id, $floorPlan);
    }

    $lengths = new RouteLengthsBetweenSectors(new FindRouteBetweenSectors($stores, $grids, $routes, new AStarPathfinder(new ManhattanDistance)));
    $planVisit = new PlanVisit($stores, new InMemoryAssortmentRepository($assortment), $lists, new VisitPlanner($strategy, $lengths));

    return [$planVisit, $routes, $lists];
}

/**
 * Plans the aisle shop's visit for user 1, whose list holds the named products.
 *
 * @return array{VisitPlan, InMemoryRouteCache}
 */
function aisleVisit(VisitOrderingStrategy $strategy, string ...$products): array
{
    [$planVisit, $routes, $lists] = visitPlanning(AisleShop::store(), AisleShop::assortment(), AisleShop::floorPlan(), $strategy);
    $lists->save(AisleShop::listOf(...$products));

    return [$planVisit->handle(new UserId(1), new StoreId(1)), $routes];
}

/**
 * The names of the sectors of the plan, in visiting order.
 *
 * @return list<string>
 */
function sectorNames(VisitPlan $plan): array
{
    return array_map(static fn (VisitStop $stop): string => $stop->sector->name->value, $plan->stops);
}

it('orders the sectors of a drawn aisle by nearest neighbour, on the lengths of the routes A* finds', function (): void {
    [$plan] = aisleVisit(new NearestNeighbourOrdering, 'Apples', 'Milk', 'Batteries', 'Bread', 'Cheese');

    expect(sectorNames($plan))->toBe(['Bakery', 'Dairy', 'Produce'])
        ->and($plan->length)->toBe(10)
        ->and($plan->stops[1]->items ?? [])->toHaveCount(2)
        ->and($plan->unsold[0]->productName->value ?? null)->toBe('Batteries');
});

it('orders them shorter with 2-opt over nearest neighbour', function (): void {
    [$plan] = aisleVisit(new TwoOptOrdering(new NearestNeighbourOrdering), 'Apples', 'Milk', 'Bread');

    expect(sectorNames($plan))->toBe(['Dairy', 'Bakery', 'Produce'])
        ->and($plan->length)->toBe(8);
});

it('keeps the routes it measured, so the next plan searches none', function (): void {
    [, $routes] = aisleVisit(new NearestNeighbourOrdering, 'Apples', 'Bread');

    expect($routes->find(new StoreId(1), new SectorId(1), new SectorId(4)))->toBeInstanceOf(Route::class)
        ->and($routes->find(new StoreId(1), new SectorId(4), new SectorId(2))?->length())->toBe(3)
        ->and($routes->find(new StoreId(1), new SectorId(4), new SectorId(1)))->toBeNull();
});

it('reports a sector to visit that no walkway reaches', function (): void {
    $assortment = new Assortment(
        new StoreId(1),
        new SectorProduct(new SectorId(2), AisleShop::product('Bread')->id),
        new SectorProduct(new SectorId(3), AisleShop::product('Milk')->id),
    );
    [$planVisit, , $lists] = visitPlanning(CornerShop::store(), $assortment, CornerShop::floorPlan(), new NearestNeighbourOrdering);
    $lists->save(AisleShop::listOf('Bread', 'Milk'));

    expect(fn (): VisitPlan => $planVisit->handle(new UserId(1), new StoreId(1)))
        ->toThrow(RouteNotFound::class, 'No walkway leads from (1, 1) to (1, 5).');
});

it('needs the store floor plan mapped once the list has something the store sells', function (): void {
    [$planVisit, , $lists] = visitPlanning(AisleShop::store(), AisleShop::assortment(), null, new NearestNeighbourOrdering);
    $lists->save(AisleShop::listOf('Bread'));

    expect(fn (): VisitPlan => $planVisit->handle(new UserId(1), new StoreId(1)))->toThrow(GridNotMapped::class);
});

it('reports a store that does not exist', function (): void {
    [$planVisit] = visitPlanning(AisleShop::store(), AisleShop::assortment(), AisleShop::floorPlan(), new NearestNeighbourOrdering);

    expect(fn (): VisitPlan => $planVisit->handle(new UserId(1), new StoreId(9)))
        ->toThrow(StoreNotFound::class, 'No store has id 9.');
});

it('plans an empty visit for a user who has no list', function (): void {
    [$planVisit] = visitPlanning(AisleShop::store(), AisleShop::assortment(), null, new NearestNeighbourOrdering);

    $plan = $planVisit->handle(new UserId(2), new StoreId(1));

    expect($plan->stops)->toBe([])
        ->and($plan->unsold)->toBe([])
        ->and($plan->start->name->value)->toBe('Entrance');
});
