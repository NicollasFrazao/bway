<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Grid\CellKind;
use App\Domain\Grid\Grid;
use App\Domain\Grid\GridCoordinate;
use LogicException;

/**
 * Builds grids from text pictures, one string per row: `.` floor, `#` wall, `S` sector, `P`
 * partition, `o` walkway, and `A` and `B` walkway cells the test wants to find (usually the start
 * and the target).
 */
final class GridDrawing
{
    /**
     * The kind of cell each character draws.
     */
    private const array LEGEND = [
        '.' => CellKind::Floor,
        '#' => CellKind::Wall,
        'S' => CellKind::Sector,
        'P' => CellKind::Partition,
        'o' => CellKind::Walkway,
        'A' => CellKind::Walkway,
        'B' => CellKind::Walkway,
    ];

    /**
     * The grid the picture draws.
     *
     * @param  list<string>  $picture  Rows of the legend, all of the same length.
     *
     * @throws LogicException when a character is not in the legend.
     */
    public static function grid(array $picture): Grid
    {
        return Grid::fromRows(array_map(
            static fn (string $row): array => array_map(
                static fn (string $character): CellKind => self::LEGEND[$character]
                    ?? throw new LogicException("The legend has no {$character}."),
                str_split($row),
            ),
            $picture,
        ));
    }

    /**
     * The cell where the marker appears first, reading rows from the top.
     *
     * @param  list<string>  $picture  Rows of the legend.
     *
     * @throws LogicException when the picture has no such marker.
     */
    public static function find(array $picture, string $marker): GridCoordinate
    {
        foreach ($picture as $y => $row) {
            $x = strpos($row, $marker);

            if ($x !== false) {
                return new GridCoordinate($x, $y);
            }
        }

        throw new LogicException("The picture has no {$marker}.");
    }

    /**
     * Draws a route over the picture with `*`, to compare routes as pictures.
     *
     * @param  list<string>  $picture  Rows of the legend.
     * @param  list<GridCoordinate>  $cells  The route's cells, all on the picture.
     * @return list<string>
     */
    public static function trace(array $picture, array $cells): array
    {
        foreach ($cells as $cell) {
            $picture = array_replace($picture, [
                $cell->y => substr_replace($picture[$cell->y] ?? '', '*', $cell->x, 1),
            ]);
        }

        return array_values($picture);
    }
}
