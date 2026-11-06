<?php

declare(strict_types=1);

use App\Domain\Grid\CellKind;
use App\Domain\Grid\CoordinateOutsideGrid;
use App\Domain\Grid\EmptyGrid;
use App\Domain\Grid\Grid;
use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Grid\RaggedGrid;
use Tests\Support\GridDrawing;

it('builds a grid from its rows and tells the kind of each cell', function (): void {
    $grid = GridDrawing::grid([
        '#####',
        '#.SP#',
        '#oo.#',
        '#####',
    ]);

    expect([$grid->width, $grid->height])->toBe([5, 4])
        ->and($grid->kindAt(new GridCoordinate(0, 0)))->toBe(CellKind::Wall)
        ->and($grid->kindAt(new GridCoordinate(1, 1)))->toBe(CellKind::Floor)
        ->and($grid->kindAt(new GridCoordinate(2, 1)))->toBe(CellKind::Sector)
        ->and($grid->kindAt(new GridCoordinate(3, 1)))->toBe(CellKind::Partition)
        ->and($grid->kindAt(new GridCoordinate(1, 2)))->toBe(CellKind::Walkway);
});

it('gives back its rows as they were given', function (): void {
    $rows = [
        [CellKind::Wall, CellKind::Floor, CellKind::Sector],
        [CellKind::Partition, CellKind::Walkway, CellKind::Wall],
    ];

    expect(Grid::fromRows($rows)->rows())->toBe($rows);
});

it('lets a route step only onto walkways', function (CellKind $kind, bool $walkable): void {
    expect($kind->isWalkable())->toBe($walkable)
        ->and(Grid::fromRows([[$kind]])->isWalkable(new GridCoordinate(0, 0)))->toBe($walkable);
})->with([
    'floor' => [CellKind::Floor, false],
    'wall' => [CellKind::Wall, false],
    'sector' => [CellKind::Sector, false],
    'partition' => [CellKind::Partition, false],
    'walkway' => [CellKind::Walkway, true],
]);

it('treats a cell off the grid as not walkable', function (): void {
    expect(GridDrawing::grid(['oo'])->isWalkable(new GridCoordinate(2, 0)))->toBeFalse();
});

it('refuses a grid without cells', function (): void {
    expect(fn (): Grid => Grid::fromRows([]))
        ->toThrow(EmptyGrid::class, 'A grid has at least one row and one column.')
        ->and(fn (): Grid => Grid::fromRows([[]]))
        ->toThrow(EmptyGrid::class, 'A grid has at least one row and one column.');
});

it('refuses rows of different lengths', function (): void {
    expect(fn (): Grid => GridDrawing::grid(['...', '...', '..']))
        ->toThrow(RaggedGrid::class, 'Every row of a grid has 3 cells; row 2 has 2.');
});

it('tells which cells and areas lie on it', function (): void {
    $grid = GridDrawing::grid(['....', '....', '....']);

    expect($grid->contains(new GridCoordinate(3, 2)))->toBeTrue()
        ->and($grid->contains(new GridCoordinate(4, 2)))->toBeFalse()
        ->and($grid->contains(new GridCoordinate(3, 3)))->toBeFalse()
        ->and($grid->containsArea(new GridArea(new GridCoordinate(1, 1), 3, 2)))->toBeTrue()
        ->and($grid->containsArea(new GridArea(new GridCoordinate(1, 1), 4, 2)))->toBeFalse()
        ->and($grid->containsArea(new GridArea(new GridCoordinate(1, 1), 3, 3)))->toBeFalse();
});

it('refuses to read a cell beyond its edges', function (int $x, int $y): void {
    $grid = GridDrawing::grid(['...', '...']);

    expect(fn (): CellKind => $grid->kindAt(new GridCoordinate($x, $y)))
        ->toThrow(CoordinateOutsideGrid::class, "Cell ({$x}, {$y}) is outside the 3 x 2 grid.");
})->with([
    'right of it' => [3, 0],
    'below it' => [0, 2],
]);

it('lists the neighbours of a cell right, left, down, up', function (): void {
    $grid = GridDrawing::grid(['...', '...', '...']);

    expect($grid->neighboursOf(new GridCoordinate(1, 1)))->toEqual([
        new GridCoordinate(2, 1),
        new GridCoordinate(0, 1),
        new GridCoordinate(1, 2),
        new GridCoordinate(1, 0),
    ]);
});

it('leaves out the neighbours beyond the edges', function (): void {
    $grid = GridDrawing::grid(['...', '...']);

    expect($grid->neighboursOf(new GridCoordinate(0, 0)))->toEqual([new GridCoordinate(1, 0), new GridCoordinate(0, 1)])
        ->and($grid->neighboursOf(new GridCoordinate(2, 1)))->toEqual([new GridCoordinate(1, 1), new GridCoordinate(2, 0)]);
});
