<?php

declare(strict_types=1);

use App\Domain\Grid\EmptyGridArea;
use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;

it('places a rectangle by its top-left cell', function (): void {
    $area = new GridArea(new GridCoordinate(3, 4), 1, 5);

    expect([$area->origin, $area->width, $area->height])->toEqual([new GridCoordinate(3, 4), 1, 5]);
});

it('refuses a size that covers no cell', function (int $width, int $height): void {
    expect(fn (): GridArea => new GridArea(new GridCoordinate(0, 0), $width, $height))
        ->toThrow(EmptyGridArea::class, "A grid area covers at least one cell; {$width} x {$height} covers none.");
})->with([
    'no width' => [0, 3],
    'no height' => [3, 0],
    'negative width' => [-2, 3],
]);

it('finds its middle cell, rounding down on an even side as v1 did', function (int $width, int $height, array $center): void {
    $area = new GridArea(new GridCoordinate(10, 20), $width, $height);

    expect([$area->center()->x, $area->center()->y])->toBe($center);
})->with([
    'odd sides' => [3, 5, [11, 22]],
    'even sides' => [4, 2, [12, 21]],
    'one cell' => [1, 1, [10, 20]],
]);

it('tells whether two rectangles share a cell', function (int $x, int $y, int $width, int $height, bool $overlaps): void {
    $area = new GridArea(new GridCoordinate(2, 2), 3, 3);
    $otherArea = new GridArea(new GridCoordinate($x, $y), $width, $height);

    expect($area->overlaps($otherArea))->toBe($overlaps)
        ->and($otherArea->overlaps($area))->toBe($overlaps);
})->with([
    'the same rectangle' => [2, 2, 3, 3, true],
    'one corner cell in common' => [4, 4, 2, 2, true],
    'inside it' => [3, 3, 1, 1, true],
    'touching on the right' => [5, 2, 2, 3, false],
    'touching below' => [2, 5, 3, 1, false],
    'to the left' => [0, 2, 2, 3, false],
    'above' => [2, 0, 3, 2, false],
]);
