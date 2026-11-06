<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\GridCoordinate;

/**
 * An estimate of the steps left from a cell to the target, which steers the A* search.
 *
 * It must never overestimate (be admissible), or the search may return a route longer than the
 * shortest one.
 */
interface Heuristic
{
    /**
     * Estimates the steps from one cell to another.
     *
     * @return int 0 or more; 0 for the same cell.
     */
    public function estimate(GridCoordinate $from, GridCoordinate $to): int;
}
