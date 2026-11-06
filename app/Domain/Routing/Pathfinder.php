<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\CoordinateOutsideGrid;
use App\Domain\Grid\Grid;
use App\Domain\Grid\GridCoordinate;

/**
 * Finds a route between two cells of a grid (domain service).
 *
 * A route enters only walkable cells (walkways); the start may be any cell on the grid, since the
 * shopper is already standing there.
 */
interface Pathfinder
{
    /**
     * Finds a shortest route from the start to the target.
     *
     * @throws CoordinateOutsideGrid when the start or the target is not on the grid.
     * @throws RouteNotFound when no walkable path joins them.
     */
    public function find(Grid $grid, GridCoordinate $start, GridCoordinate $target): Route;
}
