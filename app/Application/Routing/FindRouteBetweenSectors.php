<?php

declare(strict_types=1);

namespace App\Application\Routing;

use App\Domain\Routing\Pathfinder;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteCache;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * The route from the centre of one sector of a store to the centre of another, found once per
 * ordered pair and then served from the cache (use case).
 */
final readonly class FindRouteBetweenSectors
{
    /**
     * Takes the stores, their grids, the cache of routes and the search.
     */
    public function __construct(
        private StoreRepository $stores,
        private StoreGridRepository $grids,
        private RouteCache $routes,
        private Pathfinder $pathfinder,
    ) {}

    /**
     * Serves the kept route for the pair, or finds and keeps it.
     *
     * @throws StoreNotFound when no store has that id.
     * @throws SectorNotInStore when either sector is not one of the store's.
     * @throws GridNotMapped when the store's floor plan has not been mapped.
     * @throws RouteNotFound when no walkway joins the two sectors; nothing is kept.
     */
    public function handle(StoreId $storeId, SectorId $from, SectorId $to): Route
    {
        $store = $this->stores->get($storeId);
        $origin = $store->sector($from);
        $destination = $store->sector($to);
        $kept = $this->routes->find($storeId, $from, $to);

        if ($kept instanceof Route) {
            return $kept;
        }

        $route = $this->pathfinder->find(
            $this->grids->forStore($storeId),
            $origin->area->center(),
            $destination->area->center(),
        );
        $this->routes->remember($storeId, $from, $to, $route);

        return $route;
    }
}
