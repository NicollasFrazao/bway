<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Grid\GridArea;
use App\Domain\Shared\Name;

/**
 * Where stores and their layouts are kept (port; implemented in Infrastructure). Store management
 * (B7) creates stores and saves their layouts through it; the aggregate checks every layout first.
 */
interface StoreRepository
{
    /**
     * Loads one store with its sectors and partitions.
     *
     * @throws StoreNotFound when no store has that id.
     */
    public function get(StoreId $id): Store;

    /**
     * Loads every store a user can choose for a visit.
     *
     * @return list<Store> Empty when there is none.
     */
    public function all(): array;

    /**
     * Creates a store with its entrance, named "Entrance", and gives the aggregate with its ids.
     */
    public function add(Name $name, GridArea $entrance): Store;

    /**
     * Stores the layout of the store as it is now: sectors and partitions it no longer has are
     * deleted, with what refers to them (assortment, routes); new ones are added with their ids.
     *
     * @throws StoreNotFound when no store has its id.
     */
    public function save(Store $store): void;

    /**
     * An id no sector has yet, for a sector about to be added.
     */
    public function nextSectorId(): SectorId;

    /**
     * An id no partition has yet, for a partition about to be added.
     */
    public function nextPartitionId(): PartitionId;
}
