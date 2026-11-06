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
