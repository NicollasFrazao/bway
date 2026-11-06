<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Routing\Route;
use App\Domain\Routing\RouteCache;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;

/**
 * Keeps routes in memory, by store and ordered pair of sectors.
 */
final class InMemoryRouteCache implements RouteCache
{
    /**
     * The routes, keyed by store id, then by "from-to".
     *
     * @var array<int, array<string, Route>>
     */
    private array $routes = [];

    /**
     * The route kept for the pair, if any.
     */
    public function find(StoreId $storeId, SectorId $from, SectorId $to): ?Route
    {
        return $this->routes[$storeId->value]["{$from->value}-{$to->value}"] ?? null;
    }

    /**
     * Keeps the route for the pair.
     */
    public function remember(StoreId $storeId, SectorId $from, SectorId $to, Route $route): void
    {
        $this->routes[$storeId->value]["{$from->value}-{$to->value}"] = $route;
    }

    /**
     * Drops the store's routes.
     */
    public function forgetStore(StoreId $storeId): void
    {
        unset($this->routes[$storeId->value]);
    }
}
