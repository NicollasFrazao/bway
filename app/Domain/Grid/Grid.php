<?php

declare(strict_types=1);

namespace App\Domain\Grid;

/**
 * A mapped store: a rectangle of cells, each free floor, wall, sector outline, partition or walkway.
 *
 * Immutable. A store's grid has about a quarter of a million cells, so each row is kept as a string
 * of cell codes rather than as objects.
 */
final readonly class Grid
{
    /**
     * Wraps rows already checked and encoded.
     *
     * @param  list<string>  $rows  One string per row, one CellKind value per character.
     * @param  int  $width  Columns, at least 1.
     * @param  int  $height  Rows, at least 1.
     */
    private function __construct(
        private array $rows,
        public int $width,
        public int $height,
    ) {}

    /**
     * Builds a grid from its rows, top to bottom, each listing its cells left to right.
     *
     * @param  list<list<CellKind>>  $rows  At least one row, all as long as the first.
     *
     * @throws EmptyGrid when there is no row, or the first row has no cell.
     * @throws RaggedGrid when a row is longer or shorter than the first.
     */
    public static function fromRows(array $rows): self
    {
        $width = count($rows[0] ?? []);

        if ($width === 0) {
            throw EmptyGrid::withoutCells();
        }

        $encoded = [];

        foreach ($rows as $index => $row) {
            if (count($row) !== $width) {
                throw RaggedGrid::atRow($index, count($row), $width);
            }

            $encoded[] = implode('', array_map(static fn (CellKind $kind): int => $kind->value, $row));
        }

        return new self($encoded, $width, count($encoded));
    }

    /**
     * Tells whether the cell lies on the grid.
     */
    public function contains(GridCoordinate $cell): bool
    {
        return $this->containsPoint($cell->x, $cell->y);
    }

    /**
     * Tells whether every cell of the area lies on the grid.
     */
    public function containsArea(GridArea $area): bool
    {
        return $area->origin->x + $area->width <= $this->width
            && $area->origin->y + $area->height <= $this->height;
    }

    /**
     * Refuses a cell that does not lie on the grid.
     *
     * @throws CoordinateOutsideGrid when the cell is beyond the right or the bottom edge.
     */
    public function ensureContains(GridCoordinate $cell): void
    {
        if (! $this->contains($cell)) {
            throw CoordinateOutsideGrid::at($cell, $this->width, $this->height);
        }
    }

    /**
     * What the cell is.
     *
     * @throws CoordinateOutsideGrid when the cell is beyond the right or the bottom edge.
     */
    public function kindAt(GridCoordinate $cell): CellKind
    {
        $code = $this->rows[$cell->y][$cell->x] ?? throw CoordinateOutsideGrid::at($cell, $this->width, $this->height);

        return CellKind::from((int) $code);
    }

    /**
     * Tells whether a route may step onto the cell; a cell off the grid is not walkable.
     */
    public function isWalkable(GridCoordinate $cell): bool
    {
        return $this->contains($cell)
            && $this->kindAt($cell)->isWalkable();
    }

    /**
     * The cells one step away, on the grid, in the order right, left, down, up (v1's order, which
     * decides between routes of equal length).
     *
     * @return list<GridCoordinate> Two to four cells; fewer on an edge or a one-cell-wide grid.
     */
    public function neighboursOf(GridCoordinate $cell): array
    {
        $candidates = [
            [$cell->x + 1, $cell->y],
            [$cell->x - 1, $cell->y],
            [$cell->x, $cell->y + 1],
            [$cell->x, $cell->y - 1],
        ];
        $neighbours = [];

        foreach ($candidates as [$x, $y]) {
            if (! $this->containsPoint($x, $y)) {
                continue;
            }

            $neighbours[] = new GridCoordinate($x, $y);
        }

        return $neighbours;
    }

    /**
     * Every cell, row by row from the top, each row left to right.
     *
     * @return list<list<CellKind>>
     */
    public function rows(): array
    {
        return array_map(
            static fn (string $row): array => array_map(
                static fn (string $code): CellKind => CellKind::from((int) $code),
                str_split($row),
            ),
            $this->rows,
        );
    }

    /**
     * Tells whether a pair of integers, possibly negative, addresses a cell of the grid.
     */
    private function containsPoint(int $x, int $y): bool
    {
        return $x >= 0
            && $y >= 0
            && $x < $this->width
            && $y < $this->height;
    }
}
