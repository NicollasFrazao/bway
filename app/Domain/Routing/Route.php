<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\GridCoordinate;

/**
 * A walk across a store's grid, one step at a time, from a start cell to a target cell.
 */
final readonly class Route
{
    /**
     * The cells walked, the start first and the target last.
     *
     * @var non-empty-list<GridCoordinate>
     */
    public array $cells;

    /**
     * The last cell.
     */
    private GridCoordinate $target;

    /**
     * Lays out the walk.
     *
     * @param  list<GridCoordinate>  $cells  From the start to the target, both included; a single
     *                                       cell when the start is the target.
     *
     * @throws BrokenRoute when there is no cell, or two consecutive cells are not one step apart.
     */
    public function __construct(array $cells)
    {
        if ($cells === []) {
            throw BrokenRoute::withoutCells();
        }

        $previous = $cells[0];

        foreach (array_slice($cells, 1) as $index => $cell) {
            if (! $previous->isNextTo($cell)) {
                throw BrokenRoute::gapAfterStep($index, $previous, $cell);
            }

            $previous = $cell;
        }

        $this->cells = $cells;
        $this->target = $previous;
    }

    /**
     * Where the walk begins.
     */
    public function start(): GridCoordinate
    {
        return $this->cells[0];
    }

    /**
     * Where the walk ends.
     */
    public function target(): GridCoordinate
    {
        return $this->target;
    }

    /**
     * The number of steps: one less than the cells walked, 0 when the start is the target.
     */
    public function length(): int
    {
        return count($this->cells) - 1;
    }
}
