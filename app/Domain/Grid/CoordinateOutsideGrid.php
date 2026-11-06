<?php

declare(strict_types=1);

namespace App\Domain\Grid;

use App\Domain\Shared\DomainError;

/**
 * A cell was looked up beyond the right or the bottom edge of a grid.
 */
final class CoordinateOutsideGrid extends DomainError
{
    /**
     * Builds the error for the refused cell and the size of the grid.
     */
    public static function at(GridCoordinate $cell, int $width, int $height): self
    {
        return new self(sprintf(
            'Cell (%d, %d) is outside the %d x %d grid.',
            $cell->x,
            $cell->y,
            $width,
            $height,
        ));
    }
}
