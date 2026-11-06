<?php

declare(strict_types=1);

namespace App\Domain\Grid;

/**
 * What one cell of a mapped store is; the values are the codes v1's mapping wrote.
 */
enum CellKind: int
{
    /**
     * Open floor with nothing painted on it; routes do not cross it.
     */
    case Floor = 0;

    /**
     * The outer wall of the store.
     */
    case Wall = 1;

    /**
     * The outline of a sector.
     */
    case Sector = 2;

    /**
     * A shelf or divider.
     */
    case Partition = 3;

    /**
     * A walkway painted on the floor plan: the only kind of cell a route may enter.
     */
    case Walkway = 4;

    /**
     * Tells whether a route may step onto a cell of this kind.
     */
    public function isWalkable(): bool
    {
        return $this === self::Walkway;
    }
}
