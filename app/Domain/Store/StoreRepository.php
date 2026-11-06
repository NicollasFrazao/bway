<?php

declare(strict_types=1);

namespace App\Domain\Store;

/**
 * Where stores and their layouts are kept (port; implemented in Infrastructure).
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
}
