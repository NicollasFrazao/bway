<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;

/**
 * Keeps the route between each ordered pair of sectors of a store once it has been found, so the
 * search runs once per pair (port; implemented in Infrastructure).
 *
 * The cache is only as good as the grid and the layout the routes were found on: mapping a store's
 * floor plan again, or changing its layout, forgets the store's routes. It also keeps the pairs found
 * to have no route, so generating a store's routes shows which sectors cannot be reached.
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
     * Keeps that no walkway joins the pair, replacing a route kept before.
     */
    public function rememberUnreachable(StoreId $storeId, SectorId $from, SectorId $to): void;

    /**
     * Tells whether the pair was found to have no route.
     */
    public function isUnreachable(StoreId $storeId, SectorId $from, SectorId $to): bool;

    /**
     * How many routes are kept for the store.
     */
    public function countFound(StoreId $storeId): int;

    /**
     * The pairs of the store found to have no route.
     *
     * @return list<array{SectorId, SectorId}> From and to.
     */
    public function unreachable(StoreId $storeId): array;

    /**
     * Drops every route, and every pair without one, kept for the store.
     */
    public function forgetStore(StoreId $storeId): void;
}
