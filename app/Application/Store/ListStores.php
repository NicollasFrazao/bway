<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Store\Store;
use App\Domain\Store\StoreRepository;

/**
 * Every store, for the administrators' store management (use case).
 */
final readonly class ListStores
{
    /**
     * Takes the stores.
     */
    public function __construct(private StoreRepository $stores) {}

    /**
     * The stores with their layouts.
     *
     * @return list<Store>
     */
    public function handle(): array
    {
        return $this->stores->all();
    }
}
