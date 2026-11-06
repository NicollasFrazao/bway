<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\GridCoordinate;
use App\Domain\Shared\DomainError;

/**
 * A route was laid out with no cell, or with a jump between two cells that are not neighbours.
 */
final class BrokenRoute extends DomainError
{
    /**
     * Builds the error for a route with no cell.
     */
    public static function withoutCells(): self
    {
        return new self('A route has at least one cell: its start.');
    }

    /**
     * Builds the error for the first jump found.
     *
     * @param  int  $step  Index of the cell the jump leaves from, the start being 0.
     */
    public static function gapAfterStep(int $step, GridCoordinate $from, GridCoordinate $to): self
    {
        return new self(sprintf(
            'A route moves one cell at a time; after cell %d it jumps from (%d, %d) to (%d, %d).',
            $step,
            $from->x,
            $from->y,
            $to->x,
            $to->y,
        ));
    }
}
