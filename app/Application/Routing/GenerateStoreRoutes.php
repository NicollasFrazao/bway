<?php

declare(strict_types=1);

namespace App\Application\Routing;

use App\Domain\Routing\RouteCache;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use LogicException;

/**
 * Finds the route of every ordered pair of a store's sectors not kept yet, and keeps the pairs that
 * have none (use case; run by a queued job). Afterwards every visit of the store reads its routes.
 */
final readonly class GenerateStoreRoutes
{
    /**
     * Takes the stores, the route search and the cache.
     */
    public function __construct(
        private StoreRepository $stores,
        private FindRouteBetweenSectors $find,
        private RouteCache $routes,
    ) {}

    /**
     * Searches every pair still unknown.
     *
     * @throws StoreNotFound when no store has the id.
     * @throws GridNotMapped when the store's plan has not been mapped.
     */
    public function handle(StoreId $storeId): void
    {
        $sectors = $this->stores->get($storeId)->sectors();

        foreach ($sectors as $from) {
            foreach ($sectors as $to) {
                if (
                    $from->id->value === $to->id->value
                    || $this->routes->isUnreachable($storeId, $from->id, $to->id)
                ) {
                    continue;
                }

                try {
                    $this->find->handle($storeId, $from->id, $to->id);
                } catch (RouteNotFound) {
                    $this->routes->rememberUnreachable($storeId, $from->id, $to->id);
                } catch (SectorNotInStore $impossible) {
                    // Both sectors come from the store itself.
                    throw new LogicException($impossible->getMessage(), 0, $impossible);
                }
            }
        }
    }
}
