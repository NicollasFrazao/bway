<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Store\StoreId;

/**
 * Hands the finding of a store's routes to a worker, so the request that asks for it does not wait for
 * every A* search (port; implemented in Infrastructure on Laravel's queue).
 */
interface RouteGenerationQueue
{
    /**
     * Queues the finding of every route of the store not kept yet.
     */
    public function push(StoreId $storeId): void;
}
