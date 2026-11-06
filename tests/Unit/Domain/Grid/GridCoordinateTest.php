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

it('compares two cells by their coordinates', function (): void {
    $cell = new GridCoordinate(3, 4);

    expect($cell->equals(new GridCoordinate(3, 4)))->toBeTrue()
        ->and($cell->equals(new GridCoordinate(4, 3)))->toBeFalse()
        ->and($cell->equals(new GridCoordinate(3, 5)))->toBeFalse();
});

it('tells whether another cell is one step away, not diagonally', function (int $x, int $y, bool $next): void {
    expect(new GridCoordinate(3, 4)->isNextTo(new GridCoordinate($x, $y)))->toBe($next);
})->with([
    'right' => [4, 4, true],
    'left' => [2, 4, true],
    'below' => [3, 5, true],
    'above' => [3, 3, true],
    'the same cell' => [3, 4, false],
    'diagonal' => [4, 5, false],
    'two steps away' => [5, 4, false],
]);
