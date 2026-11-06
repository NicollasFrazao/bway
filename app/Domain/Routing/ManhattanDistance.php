<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\GridCoordinate;

/**
 * The steps between two cells when nothing is in the way and moves are only across or down: the
 * heuristic v1 used, exact on an open grid and never an overestimate.
 */
final readonly class ManhattanDistance implements Heuristic
{
    /**
     * The columns plus the rows between the two cells.
     */
    public function estimate(GridCoordinate $from, GridCoordinate $to): int
    {
        return abs($from->x - $to->x) + abs($from->y - $to->y);
    }
}
