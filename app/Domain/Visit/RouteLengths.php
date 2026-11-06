<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\Routing\RouteNotFound;
use App\Domain\Shared\DomainError;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;

/**
 * The length, in steps, of the route from one sector of a store to another (port).
 *
 * The adapter finds the route with A* or serves the one the route cache kept. A pair without a
 * route is an error, never a length: v1 counted a missing route as 0 and sent the shopper there
 * first.
 */
interface RouteLengths
{
    /**
     * The steps from the centre of one sector to the centre of the other; 0 from a sector to itself.
     *
     * @throws RouteNotFound when no walkway joins the two sectors.
     * @throws DomainError when the store, either sector or the store's grid cannot be found.
     */
    public function between(StoreId $storeId, SectorId $from, SectorId $to): int;
}
