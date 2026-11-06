<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\Routing\RouteNotFound;
use App\Domain\Shared\DomainError;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\Store\Assortment;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\Store;
use LogicException;

/**
 * Turns a shopping list into a visit through a store (domain service): groups the items by the
 * sector that sells them, reports the ones the store does not sell, and orders the sectors from the
 * entrance with the injected strategy, on real route lengths.
 */
final readonly class VisitPlanner
{
    /**
     * Takes the ordering strategy and the source of route lengths.
     */
    public function __construct(
        private VisitOrderingStrategy $strategy,
        private RouteLengths $routeLengths,
    ) {}

    /**
     * Plans the visit of the store for the list, starting at the store's entrance.
     *
     * @throws SectorNotInStore when the assortment places a listed product in a sector the store
     *                          does not have.
     * @throws RouteNotFound when a sector to visit cannot be reached from the entrance or from
     *                       another sector to visit.
     * @throws DomainError when the store or its grid cannot be found by the route lengths.
     * @throws LogicException when the strategy does not return every stop exactly once.
     */
    public function plan(Store $store, Assortment $assortment, ShoppingList $list): VisitPlan
    {
        [$itemsBySector, $unsold] = $this->group($assortment, $list);
        $pending = [];

        foreach ($itemsBySector as $id => $items) {
            $pending[$id] = new VisitStop($store->sector(new SectorId($id)), $items);
        }

        $entrance = $store->entrance();
        $stops = array_values(array_map(static fn (VisitStop $stop): SectorId => $stop->sector->id, $pending));
        $distances = DistanceTable::measure($this->routeLengths, $store->id, $entrance->id, $stops);
        $order = $this->strategy->order($entrance->id, $stops, $distances);

        return new VisitPlan(
            $entrance,
            $this->arrange($pending, $order),
            $unsold,
            $distances->through([$entrance->id, ...$order]),
        );
    }

    /**
     * The items keyed by the id of the sector selling them, by ascending id, and the unsold items.
     *
     * @return array{array<int, non-empty-list<ListItem>>, list<ListItem>}
     */
    private function group(Assortment $assortment, ShoppingList $list): array
    {
        $itemsBySector = [];
        $unsold = [];

        foreach ($list->items() as $item) {
            $sectorId = $assortment->sectorSelling($item->productId);

            if (! $sectorId instanceof SectorId) {
                $unsold[] = $item;

                continue;
            }

            $itemsBySector[$sectorId->value][] = $item;
        }

        ksort($itemsBySector);

        return [$itemsBySector, $unsold];
    }

    /**
     * The stops in the order the strategy chose, refusing an order that drops, repeats or invents
     * a stop.
     *
     * @param  array<int, VisitStop>  $pending  The stops, keyed by sector id.
     * @param  list<SectorId>  $order  What the strategy returned.
     * @return list<VisitStop>
     *
     * @throws LogicException when the order is not the stops rearranged.
     */
    private function arrange(array $pending, array $order): array
    {
        $arranged = [];

        foreach ($order as $id) {
            $arranged[] = $pending[$id->value]
                ?? throw new LogicException(sprintf('The ordering strategy returned sector %d, which is not a stop left to visit.', $id->value));
            unset($pending[$id->value]);
        }

        if ($pending !== []) {
            throw new LogicException(sprintf('The ordering strategy left out sector %s.', implode(', ', array_keys($pending))));
        }

        return $arranged;
    }
}
