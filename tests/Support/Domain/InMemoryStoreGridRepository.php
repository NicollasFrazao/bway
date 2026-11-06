<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Grid\Grid;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;

/**
 * Keeps the grids of stores in memory.
 */
final class InMemoryStoreGridRepository implements StoreGridRepository
{
    /**
     * The grids, keyed by store id.
     *
     * @var array<int, Grid>
     */
    private array $grids = [];

    /**
     * Keeps the grid, replacing the store's earlier one.
     */
    public function save(StoreId $storeId, Grid $grid): void
    {
        $this->grids[$storeId->value] = $grid;
    }

    /**
     * The store's grid.
     *
     * @throws GridNotMapped when none was saved for the store.
     */
    public function forStore(StoreId $storeId): Grid
    {
        return $this->grids[$storeId->value] ?? throw GridNotMapped::forStore($storeId);
    }
}
