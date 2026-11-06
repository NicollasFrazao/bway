<?php

declare(strict_types=1);

use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\BrokenRoute;
use App\Domain\Routing\Route;

it('walks from its first cell to its last, one step per cell after the first', function (): void {
    $route = new Route([new GridCoordinate(0, 0), new GridCoordinate(1, 0), new GridCoordinate(1, 1)]);

    expect($route->start())->toEqual(new GridCoordinate(0, 0))
        ->and($route->target())->toEqual(new GridCoordinate(1, 1))
        ->and($route->length())->toBe(2);
});

it('has length 0 when the start is the target', function (): void {
    $route = new Route([new GridCoordinate(4, 4)]);

    expect($route->length())->toBe(0)
        ->and($route->start())->toEqual($route->target());
});

it('refuses a route without cells', function (): void {
    expect(fn (): Route => new Route([]))
        ->toThrow(BrokenRoute::class, 'A route has at least one cell: its start.');
});

it('refuses a jump between cells that are not neighbours', function (GridCoordinate $next): void {
    $cells = [new GridCoordinate(0, 0), new GridCoordinate(1, 0), $next];

    expect(fn (): Route => new Route($cells))
        ->toThrow(BrokenRoute::class, "A route moves one cell at a time; after cell 1 it jumps from (1, 0) to ({$next->x}, {$next->y}).");
})->with([
    'diagonally' => [new GridCoordinate(2, 1)],
    'two cells ahead' => [new GridCoordinate(3, 0)],
    'standing still' => [new GridCoordinate(1, 0)],
]);
