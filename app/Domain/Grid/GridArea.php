<?php

declare(strict_types=1);

namespace App\Domain\Grid;

/**
 * A rectangle of cells on a store's grid, such as the floor of a sector or the footprint of a partition.
 */
final readonly class GridArea
{
    /**
     * Dividing a side by it gives the offset of its middle cell.
     */
    private const int HALF = 2;

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

    /**
     * The middle cell, where routes to and from the area start and end; on an even side, the
     * cell just past the middle (v1's rounding: origin plus half the side, rounded down).
     */
    public function center(): GridCoordinate
    {
        return new GridCoordinate(
            $this->origin->x + intdiv($this->width, self::HALF),
            $this->origin->y + intdiv($this->height, self::HALF),
        );
    }

    /**
     * Tells whether the two rectangles share at least one cell.
     */
    public function overlaps(self $other): bool
    {
        return $this->origin->x < $other->origin->x + $other->width
            && $other->origin->x < $this->origin->x + $this->width
            && $this->origin->y < $other->origin->y + $other->height
            && $other->origin->y < $this->origin->y + $this->height;
    }
}
