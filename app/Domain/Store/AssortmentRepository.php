<?php

declare(strict_types=1);

namespace App\Domain\Store;

/**
 * Where the assortment of each store is kept (port; implemented in Infrastructure).
 */
interface AssortmentRepository
{
    /**
     * Loads what a store sells and where; a store with nothing recorded gets an empty assortment.
     */
    public function forStore(StoreId $storeId): Assortment;
}
