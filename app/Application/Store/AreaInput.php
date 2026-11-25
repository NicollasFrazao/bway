<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Grid\InvalidGridArea;

/**
 * A rectangle on the grid as a form sends it: the top-left cell, a width and a height, in cells.
 */
final readonly class AreaInput
{
    /**
     * Holds the numbers typed.
     */
    public function __construct(
        public int $x,
        public int $y,
        public int $width,
        public int $height,
    ) {}

    /**
     * The rectangle the numbers describe.
     *
     * @throws InvalidGridArea when x or y is negative, or the width or the height is below one.
     */
    public function area(): GridArea
    {
        return new GridArea(new GridCoordinate($this->x, $this->y), $this->width, $this->height);
    }
}
