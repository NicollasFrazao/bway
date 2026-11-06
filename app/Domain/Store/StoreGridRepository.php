<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Grid\Grid;

/**
 * Where the mapped grid of each store is kept, under the store's id (port; implemented in
 * Infrastructure).
 *
 * One key for writing and reading: v1 wrote every mapping to `mapeamento.json` and read
 * `<store>.json`, so a new mapping never reached the routing.
 */
interface StoreGridRepository
{
    /**
     * Keeps the grid of a store, replacing the one it had.
     */
    public function save(StoreId $storeId, Grid $grid): void;

    /**
     * Loads the grid of a store.
     *
     * @throws GridNotMapped when the store's floor plan has not been mapped.
     */
    public function forStore(StoreId $storeId): Grid;
}
