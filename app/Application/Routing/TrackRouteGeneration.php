<?php

declare(strict_types=1);

namespace App\Application\Routing;

use App\Domain\Routing\RouteCache;
use App\Domain\Routing\RouteGenerationQueue;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use LogicException;

/**
 * Starts the finding of a store's routes on the queue, and tells how far it has come (use case).
 */
final readonly class TrackRouteGeneration
{
    /**
     * Takes the stores, the route cache and the queue.
     */
    public function __construct(
        private StoreRepository $stores,
        private RouteCache $routes,
        private RouteGenerationQueue $queue,
    ) {}

    /**
     * Queues the generation.
     *
     * @throws StoreNotFound when no store has the id.
     */
    public function start(StoreId $storeId): void
    {
        $this->stores->get($storeId);
        $this->queue->push($storeId);
    }

    /**
     * The progress, with the sectors of each pair without a route.
     *
     * @throws StoreNotFound when no store has the id.
     */
    public function progress(StoreId $storeId): RouteGenerationView
    {
        $store = $this->stores->get($storeId);
        $sectors = count($store->sectors());

        try {
            $unreachable = array_map(
                static fn (array $pair): array => [$store->sector($pair[0]), $store->sector($pair[1])],
                $this->routes->unreachable($storeId),
            );
        } catch (SectorNotInStore $impossible) {
            // The cache's foreign keys drop a pair with its sector.
            throw new LogicException($impossible->getMessage(), 0, $impossible);
        }

        return new RouteGenerationView($sectors * ($sectors - 1), $this->routes->countFound($storeId), $unreachable);
    }
}
