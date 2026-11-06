<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\Grid;
use App\Domain\Grid\GridCoordinate;
use SplMinHeap;

/**
 * One run of the A* search towards a target; AStarPathfinder creates one per route.
 *
 * Cells are keyed by their index on the grid (y * width + x). A cell may be queued more than once,
 * each time with fewer steps; it is expanded once, the first time it leaves the queue, which is with
 * its fewest steps because the heuristic never overestimates.
 */
final class AStarSearch
{
    /**
     * Cells waiting to be expanded, as [steps + estimate, estimate, queue order, steps, cell].
     *
     * A min-heap compares the arrays entry by entry, so the cell handed out first has the lowest
     * estimated total, then the lowest estimate left, then was queued first. The queue order is
     * unique, so the comparison never reaches the cell itself.
     *
     * @var SplMinHeap<array{int, int, int, int, GridCoordinate}>
     */
    private readonly SplMinHeap $open;

    /**
     * Fewest steps found so far to each reached cell.
     *
     * @var array<int, int>
     */
    private array $steps = [];

    /**
     * The cell each reached cell was entered from, on the fewest steps found.
     *
     * @var array<int, GridCoordinate>
     */
    private array $cameFrom = [];

    /**
     * Cells already expanded.
     *
     * @var array<int, true>
     */
    private array $closed = [];

    /**
     * Cells queued so far, which orders cells of equal priority first come, first served.
     */
    private int $queued = 0;

    /**
     * Prepares a search on the grid towards the target.
     */
    public function __construct(
        private readonly Grid $grid,
        private readonly GridCoordinate $target,
        private readonly Heuristic $heuristic,
    ) {
        $this->open = new SplMinHeap;
    }

    /**
     * Runs the search from the start.
     *
     * @throws RouteNotFound when no walkable path joins the start to the target.
     */
    public function from(GridCoordinate $start): Route
    {
        $this->reach($start, 0, null);

        while (! $this->open->isEmpty()) {
            [, , , $steps, $current] = $this->open->extract();

            if ($current->equals($this->target)) {
                return $this->routeTo($current);
            }

            $this->expand($current, $steps);
        }

        throw RouteNotFound::between($start, $this->target);
    }

    /**
     * Queues the walkable neighbours of a cell that this cell reaches in fewer steps than before.
     */
    private function expand(GridCoordinate $cell, int $steps): void
    {
        $key = $this->key($cell);

        if (isset($this->closed[$key])) {
            return;
        }

        $this->closed[$key] = true;

        foreach ($this->grid->neighboursOf($cell) as $neighbour) {
            if (! $this->isWorthQueueing($neighbour, $steps + 1)) {
                continue;
            }

            $this->reach($neighbour, $steps + 1, $cell);
        }
    }

    /**
     * Tells whether a neighbour may be entered and is reached in fewer steps than before.
     */
    private function isWorthQueueing(GridCoordinate $cell, int $steps): bool
    {
        $key = $this->key($cell);

        return ! isset($this->closed[$key])
            && $steps < ($this->steps[$key] ?? PHP_INT_MAX)
            && $this->grid->isWalkable($cell);
    }

    /**
     * Records the steps to a cell and where it was entered from, and queues it.
     */
    private function reach(GridCoordinate $cell, int $steps, ?GridCoordinate $from): void
    {
        $key = $this->key($cell);
        $estimate = $this->heuristic->estimate($cell, $this->target);
        $this->steps[$key] = $steps;

        if ($from instanceof GridCoordinate) {
            $this->cameFrom[$key] = $from;
        }

        $this->open->insert([$steps + $estimate, $estimate, $this->queued, $steps, $cell]);
        $this->queued++;
    }

    /**
     * Walks back from the target to the start along the cells each was entered from.
     */
    private function routeTo(GridCoordinate $target): Route
    {
        $cells = [$target];
        $key = $this->key($target);

        while (isset($this->cameFrom[$key])) {
            $cell = $this->cameFrom[$key];
            $cells[] = $cell;
            $key = $this->key($cell);
        }

        return new Route(array_reverse($cells));
    }

    /**
     * The index of a cell on the grid.
     */
    private function key(GridCoordinate $cell): int
    {
        return $cell->y * $this->grid->width + $cell->x;
    }
}
