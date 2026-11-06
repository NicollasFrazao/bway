<?php

declare(strict_types=1);

namespace App\Domain\Grid;

/**
 * One cell of a store's grid: x counts columns from the left, y counts rows from the top, both from 0.
 */
final readonly class GridCoordinate
{
    /**
     * Addresses a cell.
     *
     * @throws NegativeCoordinate when x or y is below 0.
     */
    public function __construct(
        public int $x,
        public int $y,
    ) {
        if (
            $x < 0
            || $y < 0
        ) {
            throw NegativeCoordinate::at($x, $y);
        }
    }
}
