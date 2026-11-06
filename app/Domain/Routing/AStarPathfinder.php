<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\CoordinateOutsideGrid;
use App\Domain\Grid\Grid;
use App\Domain\Grid\GridCoordinate;

/**
 * The A* search: a shortest route over walkable cells, steered by an admissible heuristic.
 *
 * Between routes of the same length it is deterministic: it prefers the cell estimated nearer the
 * target, then the cell it queued first, and queues neighbours right, left, down, up.
 */
final readonly class AStarPathfinder implements Pathfinder
{
    /**
     * Takes the estimate that steers the search (Manhattan distance in production).
     */
    public function __construct(private Heuristic $heuristic) {}

    /**
     * Finds a shortest route from the start to the target.
     *
     * @throws CoordinateOutsideGrid when the start or the target is not on the grid.
     * @throws RouteNotFound when no walkable path joins them.
     */
    public function find(Grid $grid, GridCoordinate $start, GridCoordinate $target): Route
    {
        $grid->ensureContains($start);
        $grid->ensureContains($target);

        if ($start->equals($target)) {
            return new Route([$start]);
        }

        return new AStarSearch($grid, $target, $this->heuristic)->from($start);
    }
}
