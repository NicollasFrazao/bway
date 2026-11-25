<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Domain\Routing\RouteGenerationQueue;
use App\Domain\Store\StoreId;

/**
 * Queues the generation of a store's routes as a Laravel job.
 */
final readonly class LaravelRouteGenerationQueue implements RouteGenerationQueue
{
    /**
     * Dispatches the job for the store.
     */
    public function push(StoreId $storeId): void
    {
        GenerateStoreRoutesJob::dispatch($storeId->value);
    }
}
