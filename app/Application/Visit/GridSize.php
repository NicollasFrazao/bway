<?php

declare(strict_types=1);

namespace App\Application\Visit;

/**
 * How large a store's grid is, in cells, for the map to scale to.
 */
final readonly class GridSize
{
    /**
     * Holds the size.
     */
    public function __construct(
        public int $width,
        public int $height,
    ) {}
}
