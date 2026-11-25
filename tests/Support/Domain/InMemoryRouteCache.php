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
     * The pairs without a route, keyed like the routes.
     *
     * @var array<int, array<string, array{SectorId, SectorId}>>
     */
    private array $unreachable = [];

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
        unset($this->unreachable[$storeId->value]["{$from->value}-{$to->value}"]);
    }

    /**
     * Keeps that the pair has no route.
     */
    public function rememberUnreachable(StoreId $storeId, SectorId $from, SectorId $to): void
    {
        $this->unreachable[$storeId->value]["{$from->value}-{$to->value}"] = [$from, $to];
        unset($this->routes[$storeId->value]["{$from->value}-{$to->value}"]);
    }

    /**
     * Tells whether the pair has no route.
     */
    public function isUnreachable(StoreId $storeId, SectorId $from, SectorId $to): bool
    {
        return isset($this->unreachable[$storeId->value]["{$from->value}-{$to->value}"]);
    }

    /**
     * Counts the store's routes.
     */
    public function countFound(StoreId $storeId): int
    {
        return count($this->routes[$storeId->value] ?? []);
    }

    /**
     * The store's pairs without a route.
     *
     * @return list<array{SectorId, SectorId}>
     */
    public function unreachable(StoreId $storeId): array
    {
        return array_values($this->unreachable[$storeId->value] ?? []);
    }

    /**
     * Drops the store's routes.
     */
    public function forgetStore(StoreId $storeId): void
    {
        unset($this->routes[$storeId->value], $this->unreachable[$storeId->value]);
    }
}
