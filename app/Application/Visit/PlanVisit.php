<?php

declare(strict_types=1);

namespace App\Application\Visit;

use App\Domain\Routing\RouteNotFound;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use App\Domain\Visit\VisitPlan;
use App\Domain\Visit\VisitPlanner;

/**
 * The visit of a store for a user's shopping list: its sectors in walking order from the entrance,
 * with the items to pick up in each and the items the store does not sell (use case).
 */
final readonly class PlanVisit
{
    /**
     * Takes the stores, their assortments, the shopping lists and the planner.
     */
    public function __construct(
        private StoreRepository $stores,
        private AssortmentRepository $assortments,
        private ShoppingListRepository $lists,
        private VisitPlanner $planner,
    ) {}

    /**
     * Plans the visit of the store for the user's current list.
     *
     * @throws StoreNotFound when no store has that id.
     * @throws SectorNotInStore when the assortment places a listed product in a sector the store
     *                          does not have.
     * @throws GridNotMapped when the store's floor plan has not been mapped.
     * @throws RouteNotFound when a sector to visit cannot be reached on the walkways.
     */
    public function handle(UserId $shopper, StoreId $storeId): VisitPlan
    {
        return $this->planner->plan(
            $this->stores->get($storeId),
            $this->assortments->forStore($storeId),
            $this->lists->forOwner($shopper),
        );
    }
}
