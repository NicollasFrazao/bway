<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;

/**
 * Keeps the route between each ordered pair of sectors of a store once it has been found, so the
 * search runs once per pair (port; implemented in Infrastructure).
 *
 * The cache is only as good as the grid the routes were found on: mapping a store's floor plan
 * again forgets the store's routes.
 */
interface RouteCache
{
    /**
     * The route kept for the pair, or null when none has been kept.
     */
    public function find(StoreId $storeId, SectorId $from, SectorId $to): ?Route;

    /**
     * Keeps the route for the pair, replacing any kept before.
     */
    public function remember(StoreId $storeId, SectorId $from, SectorId $to, Route $route): void;

    /**
     * Drops every route kept for the store.
     */
    public function forgetStore(StoreId $storeId): void;
}
