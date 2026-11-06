<?php

declare(strict_types=1);

namespace App\Application\Routing;

use App\Domain\Routing\RouteNotFound;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Visit\RouteLengths;

/**
 * Route lengths from the routes FindRouteBetweenSectors finds once and then serves from the route
 * cache, so a visit is ordered on the walkways, not on straight lines.
 */
final readonly class RouteLengthsBetweenSectors implements RouteLengths
{
    /**
     * Takes the use case that finds and keeps the routes.
     */
    public function __construct(private FindRouteBetweenSectors $routes) {}

    /**
     * The steps of the route between the centres of the two sectors.
     *
     * @throws StoreNotFound when no store has that id.
     * @throws SectorNotInStore when either sector is not one of the store's.
     * @throws GridNotMapped when the store's floor plan has not been mapped.
     * @throws RouteNotFound when no walkway joins the two sectors.
     */
    public function between(StoreId $storeId, SectorId $from, SectorId $to): int
    {
        return $this->routes->handle($storeId, $from, $to)->length();
    }
}
