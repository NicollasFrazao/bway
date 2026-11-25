<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Grid\GridPainter;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;

/**
 * The picture of a store's mapped grid, for laying out its sectors on it (use case).
 */
final readonly class ShowStoreGrid
{
    /**
     * Takes the grids and the painter.
     */
    public function __construct(
        private StoreGridRepository $grids,
        private GridPainter $painter,
    ) {}

    /**
     * The grid as a PNG image, one pixel per cell.
     *
     * @throws GridNotMapped when the store's plan has not been mapped.
     */
    public function handle(StoreId $storeId): string
    {
        return $this->painter->png($this->grids->forStore($storeId));
    }
}
