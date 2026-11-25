<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Application\Routing\GenerateStoreRoutes;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Finds every route of a store not kept yet, on a queue worker; one job per store at a time. The
 * worker of docker-compose.yml allows it ten minutes (`--timeout=600`): a large store has many pairs.
 */
final class GenerateStoreRoutesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /**
     * Holds the store's id.
     */
    public function __construct(public readonly int $storeId) {}

    /**
     * The key that keeps two jobs of the same store from being queued together.
     */
    public function uniqueId(): string
    {
        return (string) $this->storeId;
    }

    /**
     * Runs the use case; a store deleted meanwhile has nothing to find.
     */
    public function handle(GenerateStoreRoutes $generate): void
    {
        try {
            $generate->handle(new StoreId($this->storeId));
        } catch (StoreNotFound) {
            return;
        }
    }
}
