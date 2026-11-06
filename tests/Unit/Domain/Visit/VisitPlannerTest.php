<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductId;
use App\Domain\ShoppingList\ListItem;
use App\Domain\Store\Assortment;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\StoreId;
use App\Domain\Visit\DistanceTable;
use App\Domain\Visit\NearestNeighbourOrdering;
use App\Domain\Visit\TwoOptOrdering;
use App\Domain\Visit\VisitOrderingStrategy;
use App\Domain\Visit\VisitPlan;
use App\Domain\Visit\VisitPlanner;
use Tests\Support\AisleShop;
use Tests\Support\Domain\FixedRouteLengths;

/**
 * The steps between the sectors of the aisle shop, as its floor plan has them: they sit on one
 * walkway at y = 3 (entrance 1), 4 (bakery 2), 1 (dairy 3) and 7 (produce 4).
 */
function aisleLengths(): FixedRouteLengths
{
    $rows = [1 => 3, 2 => 4, 3 => 1, 4 => 7];
    $lengths = [];

    foreach ($rows as $from => $a) {
        foreach ($rows as $to => $b) {
            $lengths[$from][$to] = abs($a - $b);
        }
    }

    return new FixedRouteLengths($lengths);
}

/**
 * Plans the aisle shop's visit for the named products with the strategy.
 */
function aislePlan(VisitOrderingStrategy $strategy, string ...$products): VisitPlan
{
    return new VisitPlanner($strategy, aisleLengths())
        ->plan(AisleShop::store(), AisleShop::assortment(), AisleShop::listOf(...$products));
}

/**
 * Each stop as its sector's name and the names of its items.
 *
 * @return array<string, list<string>>
 */
function stopsOf(VisitPlan $plan): array
{
    $stops = [];

    foreach ($plan->stops as $stop) {
        $stops[$stop->sector->name->value] = array_map(static fn (ListItem $item): string => $item->productName->value, $stop->items);
    }

    return $stops;
}

/**
 * A strategy that returns a fixed order, whatever it is asked.
 *
 * @param  list<int>  $ids  The sector ids to return.
 */
function fixedOrder(array $ids): VisitOrderingStrategy
{
    return new readonly class($ids) implements VisitOrderingStrategy
    {
        /**
         * Holds the order.
         *
         * @param  list<int>  $ids  The sector ids to return.
         */
        public function __construct(private array $ids) {}

        /**
         * The fixed order.
         *
         * @param  list<SectorId>  $stops  The sectors to visit, ignored.
         * @return list<SectorId>
         */
        public function order(SectorId $start, array $stops, DistanceTable $distances): array
        {
            return sectorIds(...$this->ids);
        }
    };
}

it('groups the items by the sector selling them and orders the sectors by nearest neighbour', function (): void {
    $plan = aislePlan(new NearestNeighbourOrdering, 'Apples', 'Milk', 'Bread', 'Cheese');

    expect($plan->start->name->value)->toBe('Entrance')
        ->and(stopsOf($plan))->toBe([
            'Bakery' => ['Bread'],
            'Dairy' => ['Milk', 'Cheese'],
            'Produce' => ['Apples'],
        ])
        ->and($plan->length)->toBe(10)
        ->and($plan->unsold)->toBe([]);
});

it('orders the same sectors shorter with 2-opt', function (): void {
    $plan = aislePlan(new TwoOptOrdering(new NearestNeighbourOrdering), 'Apples', 'Milk', 'Bread', 'Cheese');

    expect(array_keys(stopsOf($plan)))->toBe(['Dairy', 'Bakery', 'Produce'])
        ->and($plan->length)->toBe(8);
});

it('reports the items the store does not sell, in list order, instead of dropping them', function (): void {
    $plan = aislePlan(new NearestNeighbourOrdering, 'Batteries', 'Bread');

    expect(stopsOf($plan))->toBe(['Bakery' => ['Bread']])
        ->and($plan->unsold)->toEqual([new ListItem(AisleShop::product('Batteries')->id, AisleShop::product('Batteries')->name)]);
});

it('plans no stops, and asks for no route, when the store sells nothing on the list', function (): void {
    $lengths = aisleLengths();

    $plan = new VisitPlanner(new NearestNeighbourOrdering, $lengths)
        ->plan(AisleShop::store(), AisleShop::assortment(), AisleShop::listOf('Batteries'));

    expect($plan->stops)->toBe([])
        ->and($plan->length)->toBe(0)
        ->and($plan->unsold)->toHaveCount(1)
        ->and($lengths->asked)->toBe([]);
});

it('measures only the routes the visit may take', function (): void {
    $lengths = aisleLengths();

    new VisitPlanner(new NearestNeighbourOrdering, $lengths)
        ->plan(AisleShop::store(), AisleShop::assortment(), AisleShop::listOf('Apples', 'Bread'));

    expect($lengths->asked)->toBe(['1-2', '1-4', '2-4', '4-2']);
});

it('visits the entrance first, at no cost, when it sells something on the list', function (): void {
    $assortment = new Assortment(
        new StoreId(1),
        new SectorProduct(new SectorId(1), AisleShop::product('Batteries')->id),
        new SectorProduct(new SectorId(2), AisleShop::product('Bread')->id),
    );

    $plan = new VisitPlanner(new NearestNeighbourOrdering, aisleLengths())
        ->plan(AisleShop::store(), $assortment, AisleShop::listOf('Bread', 'Batteries'));

    expect(stopsOf($plan))->toBe(['Entrance' => ['Batteries'], 'Bakery' => ['Bread']])
        ->and($plan->length)->toBe(1);
});

it('refuses an assortment that sells a listed product in a sector the store does not have', function (): void {
    $assortment = new Assortment(new StoreId(1), new SectorProduct(new SectorId(9), new ProductId(1)));

    expect(fn (): VisitPlan => new VisitPlanner(new NearestNeighbourOrdering, aisleLengths())
        ->plan(AisleShop::store(), $assortment, AisleShop::listOf('Bread')))
        ->toThrow(SectorNotInStore::class, 'Store 1 has no sector 9.');
});

it('refuses an order that is not the stops rearranged', function (string $message, int ...$order): void {
    expect(fn (): VisitPlan => aislePlan(fixedOrder(array_values($order)), 'Apples', 'Milk', 'Bread'))
        ->toThrow(LogicException::class, $message);
})->with([
    'a stop left out' => ['The ordering strategy left out sector 4.', 2, 3],
    'a stop twice' => ['The ordering strategy returned sector 3, which is not a stop left to visit.', 2, 3, 3, 4],
    'a sector that is no stop' => ['The ordering strategy returned sector 1, which is not a stop left to visit.', 2, 3, 1, 4],
]);
