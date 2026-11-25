<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Routing\RouteGenerationQueue;
use App\Domain\Store\StoreId;

/**
 * A queue that only records the stores pushed to it, for the tests to read.
 */
final class RecordingRouteGenerationQueue implements RouteGenerationQueue
{
    /**
     * The ids of the stores pushed, in order.
     *
     * @var list<int>
     */
    public array $pushed = [];

    /**
     * Records the store.
     */
    public function push(StoreId $storeId): void
    {
        $this->pushed[] = $storeId->value;
    }
}
