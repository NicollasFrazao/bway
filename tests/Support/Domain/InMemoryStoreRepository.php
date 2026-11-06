<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * Serves a fixed set of stores from memory.
 */
final readonly class InMemoryStoreRepository implements StoreRepository
{
    /**
     * The stores, in the order given.
     *
     * @var list<Store>
     */
    private array $stores;

    /**
     * Holds the stores the tests need.
     */
    public function __construct(Store ...$stores)
    {
        $this->stores = array_values($stores);
    }

    /**
     * Finds the store with that id.
     *
     * @throws StoreNotFound when none of the stores has it.
     */
    public function get(StoreId $id): Store
    {
        foreach ($this->stores as $store) {
            if ($store->id->value === $id->value) {
                return $store;
            }
        }

        throw StoreNotFound::withId($id);
    }

    /**
     * Every store, in the order given.
     *
     * @return list<Store>
     */
    public function all(): array
    {
        return $this->stores;
    }
}
