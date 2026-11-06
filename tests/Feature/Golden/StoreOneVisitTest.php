<?php

declare(strict_types=1);

use App\Application\Visit\PlanVisit;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Routing\RouteCache;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Domain\Store\Assortment;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreRepository;
use App\Domain\Visit\DistanceTable;
use App\Domain\Visit\NearestNeighbourOrdering;
use App\Domain\Visit\RouteLengths;
use App\Domain\Visit\VisitPlan;
use App\Domain\Visit\VisitPlanner;
use App\Domain\Visit\VisitStop;
use Tests\Support\Domain\InMemoryAssortmentRepository;
use Tests\Support\Domain\InMemoryRouteCache;
use Tests\Support\Domain\InMemoryShoppingListRepository;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\V1StoreOne;

/*
 * Golden test of the visit: store 1 as v1 left it (sector 2 taken as the entrance, see V1StoreOne),
 * with a list that needs every other sector. The decisions are in docs/adr/0004-visit-planning.md.
 */

/**
 * The sample list: product id => [name, the sector of store 1 that sells it, or null if none does].
 */
const STORE_ONE_SAMPLE = [
    1 => ['Bananas', 11],
    2 => ['Rice', 3],
    3 => ['Coffee', 6],
    4 => ['Light bulbs', null],
    5 => ['Milk', 10],
    6 => ['Beans', 5],
    7 => ['Pasta', 3],
];

/**
 * The order v1's greedy ordering gives on its own stored routes, from sector 2: 3 (100 steps), 6
 * (136), 5 (223), 10 (515), 11 (285), 1,259 steps in all.
 */
const V1_VISIT_ORDER = [3, 6, 5, 10, 11];

/**
 * Wires the use case in the container: store 1 with its PHP-mapped grid, the sample assortment,
 * user 1's sample list and the routes, all in memory (the seeded database runs the same visit in
 * tests/Feature/Database/SeedersTest.php).
 */
function bindStoreOneVisit(): void
{
    $grids = new InMemoryStoreGridRepository;
    $grids->save(new StoreId(1), V1StoreOne::mappedByPhp());
    $list = ShoppingList::empty(new UserId(1));
    $sectorProducts = [];

    foreach (STORE_ONE_SAMPLE as $id => [$name, $sector]) {
        $list->add(new Product(new ProductId($id), ProductName::from($name)));

        if ($sector === null) {
            continue;
        }

        $sectorProducts[] = new SectorProduct(new SectorId($sector), new ProductId($id));
    }

    $lists = new InMemoryShoppingListRepository;
    $lists->save($list);
    app()->instance(StoreRepository::class, new InMemoryStoreRepository(V1StoreOne::store()));
    app()->instance(StoreGridRepository::class, $grids);
    app()->instance(AssortmentRepository::class, new InMemoryAssortmentRepository(new Assortment(new StoreId(1), ...$sectorProducts)));
    app()->instance(ShoppingListRepository::class, $lists);
    app()->instance(RouteCache::class, new InMemoryRouteCache);
}

/**
 * The sector ids of the plan, in visiting order.
 *
 * @return list<int>
 */
function visitedSectors(VisitPlan $plan): array
{
    return array_map(static fn (VisitStop $stop): int => $stop->sector->id->value, $plan->stops);
}

/**
 * The product names of the items, in order.
 *
 * @param  list<ListItem>  $items  Any items.
 * @return list<string>
 */
function itemNames(array $items): array
{
    return array_map(static fn (ListItem $item): string => $item->productName->value, $items);
}

/**
 * Every order of the given values.
 *
 * @param  list<int>  $values  Distinct values.
 * @return list<list<int>>
 */
function permutationsOf(array $values): array
{
    if (count($values) < 2) {
        return [$values];
    }

    $permutations = [];

    foreach ($values as $index => $first) {
        $rest = $values;
        unset($rest[$index]);

        foreach (permutationsOf(array_values($rest)) as $permutation) {
            $permutations[] = [$first, ...$permutation];
        }
    }

    return $permutations;
}

/**
 * The route lengths of store 1 as v1 stored them: its files hold steps + 1 entries.
 */
function v1Lengths(): DistanceTable
{
    $lengths = [];

    foreach (V1StoreOne::SECTOR_IDS as $from) {
        foreach (V1StoreOne::SECTOR_IDS as $to) {
            $lengths[$from][$to] = max(count(V1StoreOne::route($from, $to)) - 1, 0);
        }
    }

    return new DistanceTable($lengths);
}

it('measures every route the visit may take as long as v1 stored it', function (): void {
    bindStoreOneVisit();
    $lengths = app(RouteLengths::class);
    $stored = v1Lengths();
    $differences = [];

    foreach ([2, ...V1_VISIT_ORDER] as $from) {
        foreach (array_diff(V1_VISIT_ORDER, [$from]) as $to) {
            $php = $lengths->between(new StoreId(1), new SectorId($from), new SectorId($to));
            $v1 = $stored->between(new SectorId($from), new SectorId($to));

            if ($php === $v1) {
                continue;
            }

            $differences["{$from}-{$to}"] = [$php, $v1];
        }
    }

    expect($differences)->toBe([]);
});

it('orders the visit as v1 did with nearest neighbour', function (): void {
    bindStoreOneVisit();
    $planner = new VisitPlanner(new NearestNeighbourOrdering, app(RouteLengths::class));

    $plan = $planner->plan(
        V1StoreOne::store(),
        app(AssortmentRepository::class)->forStore(new StoreId(1)),
        app(ShoppingListRepository::class)->forOwner(new UserId(1)),
    );

    expect(visitedSectors($plan))->toBe(V1_VISIT_ORDER)
        ->and($plan->length)->toBe(1259);
});

it('plans a visit 66 steps shorter than v1 through the use case, with 2-opt, the shortest of all orders', function (): void {
    bindStoreOneVisit();

    $plan = app(PlanVisit::class)->handle(new UserId(1), new StoreId(1));
    $stored = v1Lengths();
    $shortest = PHP_INT_MAX;

    foreach (permutationsOf(V1_VISIT_ORDER) as $order) {
        $shortest = min($shortest, $stored->through(array_map(static fn (int $id): SectorId => new SectorId($id), [2, ...$order])));
    }

    expect(visitedSectors($plan))->toBe([3, 5, 6, 10, 11])
        ->and($plan->length)->toBe(1193)
        ->and($shortest)->toBe(1193)
        ->and($plan->start->id->value)->toBe(2);
});

it('groups the sample list by sector and reports the product store 1 does not sell', function (): void {
    bindStoreOneVisit();

    $plan = app(PlanVisit::class)->handle(new UserId(1), new StoreId(1));

    expect(array_map(static fn (VisitStop $stop): array => itemNames($stop->items), $plan->stops))->toBe([
        ['Rice', 'Pasta'],
        ['Beans'],
        ['Coffee'],
        ['Milk'],
        ['Bananas'],
    ])
        ->and(itemNames($plan->unsold))->toBe(['Light bulbs']);
});
