<?php

declare(strict_types=1);

namespace App\Application\Visit;

use App\Application\Routing\FindRouteBetweenSectors;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Shared\UserId;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * The visit of a store for a user's list, with the walks between its sectors to draw on a map
 * (use case). It replaces v1's static map image.
 */
final readonly class MapVisit
{
    /**
     * Takes the visit planning, the route search, the stores and their grids.
     */
    public function __construct(
        private PlanVisit $plan,
        private FindRouteBetweenSectors $routes,
        private StoreRepository $stores,
        private StoreGridRepository $grids,
    ) {}

    /**
     * Plans the visit and finds each walk; the routes come from the cache the plan filled.
     *
     * @throws StoreNotFound when no store has that id.
     * @throws SectorNotInStore when the assortment places a listed product in a sector the store
     *                          does not have.
     * @throws GridNotMapped when the store's floor plan has not been mapped.
     * @throws RouteNotFound when a sector to visit cannot be reached on the walkways.
     */
    public function handle(UserId $shopper, StoreId $storeId): VisitMap
    {
        $plan = $this->plan->handle($shopper, $storeId);
        $grid = $this->grids->forStore($storeId);
        $legs = [];
        $from = $plan->start;

        foreach ($plan->stops as $stop) {
            $route = $this->routes->handle($storeId, $from->id, $stop->sector->id);
            $legs[] = new VisitLeg($from, $stop->sector, $this->corners($route->cells), $route->length());
            $from = $stop->sector;
        }

        return new VisitMap($this->stores->get($storeId), $plan, new GridSize($grid->width, $grid->height), $legs);
    }

    /**
     * The cells where a walk starts, changes direction and ends.
     *
     * @param  non-empty-list<GridCoordinate>  $cells
     * @return non-empty-list<GridCoordinate>
     */
    private function corners(array $cells): array
    {
        $corners = [$cells[0]];
        $previous = $cells[0];
        $direction = null;

        foreach (array_slice($cells, 1) as $cell) {
            $step = [$cell->x - $previous->x, $cell->y - $previous->y];

            if (
                $direction !== null
                && $step !== $direction
            ) {
                $corners[] = $previous;
            }

            $direction = $step;
            $previous = $cell;
        }

        if ($direction !== null) {
            $corners[] = $previous;
        }

        return $corners;
    }
}
