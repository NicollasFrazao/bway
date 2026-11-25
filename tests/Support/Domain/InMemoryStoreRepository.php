<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Grid\GridArea;
use App\Domain\Shared\Name;
use App\Domain\Store\Partition;
use App\Domain\Store\PartitionId;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorKind;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * Keeps stores in memory, keyed by id, in the order they were given or added.
 */
final class InMemoryStoreRepository implements StoreRepository
{
    /**
     * The stores, keyed by id.
     *
     * @var array<int, Store>
     */
    private array $stores = [];

    /**
     * Holds the stores the tests start with.
     */
    public function __construct(Store ...$stores)
    {
        foreach ($stores as $store) {
            $this->stores[$store->id->value] = $store;
        }
    }

    /**
     * Finds the store with that id.
     *
     * @throws StoreNotFound when none of the stores has it.
     */
    public function get(StoreId $id): Store
    {
        return $this->stores[$id->value] ?? throw StoreNotFound::withId($id);
    }

    /**
     * Every store, in the order given.
     *
     * @return list<Store>
     */
    public function all(): array
    {
        return array_values($this->stores);
    }

    /**
     * Adds a store with its entrance under the next ids.
     */
    public function add(Name $name, GridArea $entrance): Store
    {
        $store = new Store(
            new StoreId(count($this->stores) + 1),
            $name,
            [new Sector($this->nextSectorId(), Name::from('Entrance'), $entrance, SectorKind::Entrance)],
            [],
        );

        return $this->stores[$store->id->value] = $store;
    }

    /**
     * Replaces the store kept under its id.
     *
     * @throws StoreNotFound when none has it.
     */
    public function save(Store $store): void
    {
        $this->get($store->id);
        $this->stores[$store->id->value] = $store;
    }

    /**
     * One more than the largest sector id of any store.
     */
    public function nextSectorId(): SectorId
    {
        $ids = [0];

        foreach ($this->stores as $store) {
            foreach ($store->sectors() as $sector) {
                $ids[] = $sector->id->value;
            }
        }

        return new SectorId(max($ids) + 1);
    }

    /**
     * One more than the largest partition id of any store.
     */
    public function nextPartitionId(): PartitionId
    {
        $ids = [0];

        foreach ($this->stores as $store) {
            $ids = [...$ids, ...array_map(static fn (Partition $partition): int => $partition->id->value, $store->partitions())];
        }

        return new PartitionId(max($ids) + 1);
    }
}
