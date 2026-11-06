<?php

declare(strict_types=1);

use App\Domain\Grid\GridCoordinate;
use App\Domain\Grid\NegativeCoordinate;

it('addresses a cell, the origin included', function (): void {
    $origin = new GridCoordinate(0, 0);
    $cell = new GridCoordinate(12, 7);

    expect([$origin->x, $origin->y, $cell->x, $cell->y])->toBe([0, 0, 12, 7]);
});

it('refuses a negative x or y', function (int $x, int $y): void {
    expect(fn (): GridCoordinate => new GridCoordinate($x, $y))
        ->toThrow(NegativeCoordinate::class, "Grid coordinates start at (0, 0); ({$x}, {$y}) is outside the grid.");
})->with([
    'negative x' => [-1, 0],
    'negative y' => [0, -1],
    'both negative' => [-3, -4],
]);
