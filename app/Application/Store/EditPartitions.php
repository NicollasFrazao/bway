<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Grid\InvalidGridArea;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\Partition;
use App\Domain\Store\PartitionId;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * Adds and removes the partitions (shelves, dividers) of a store (use case).
 */
final readonly class EditPartitions
{
    /**
     * Takes the stores and the writer of layouts.
     */
    public function __construct(
        private StoreRepository $stores,
        private LayoutWriter $writer,
    ) {}

    /**
     * Adds a partition.
     *
     * @throws StoreNotFound when no store has the id.
     * @throws InvalidGridArea when the area starts at a negative cell or has no width or height.
     * @throws LayoutRefused when it overlaps another part or reaches beyond the grid.
     */
    public function add(StoreId $storeId, AreaInput $area): PartitionId
    {
        $store = $this->stores->get($storeId);
        $partition = new Partition($this->stores->nextPartitionId(), $area->area());
        $this->writer->write($store, $store->sectors(), [...$store->partitions(), $partition]);

        return $partition->id;
    }

    /**
     * Removes a partition; one the store does not have changes nothing.
     *
     * @throws StoreNotFound when no store has the id.
     * @throws LayoutRefused never in practice: removing a partition cannot break a valid layout.
     */
    public function remove(StoreId $storeId, PartitionId $partitionId): void
    {
        $store = $this->stores->get($storeId);
        $partitions = array_values(array_filter(
            $store->partitions(),
            static fn (Partition $partition): bool => $partition->id->value !== $partitionId->value,
        ));
        $this->writer->write($store, $store->sectors(), $partitions);
    }
}
