<?php

declare(strict_types=1);

namespace App\Domain\Grid;

/**
 * A rectangle of cells on a store's grid, such as the floor of a sector or the footprint of a partition.
 */
final readonly class GridArea
{
    /**
     * Places the rectangle by its top-left cell.
     *
     * @param  int  $width  Columns it covers, at least 1.
     * @param  int  $height  Rows it covers, at least 1.
     *
     * @throws EmptyGridArea when the width or the height is below 1.
     */
    public function __construct(
        public GridCoordinate $origin,
        public int $width,
        public int $height,
    ) {
        if (
            $width < 1
            || $height < 1
        ) {
            throw EmptyGridArea::withSize($width, $height);
        }
    }
}
