<?php

declare(strict_types=1);

use App\Domain\Grid\CoordinateOutsideGrid;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\Heuristic;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteNotFound;
use Tests\Support\GridDrawing;

/**
 * Finds the route from A to B on the picture, and draws it back over the picture with `*`.
 *
 * @param  list<string>  $picture  Rows of GridDrawing's legend.
 * @return list<string>
 */
function traceRoute(array $picture, ?Heuristic $heuristic = null): array
{
    return GridDrawing::trace($picture, routeOn($picture, $heuristic)->cells);
}

/**
 * Finds the route from A to B on the picture.
 *
 * @param  list<string>  $picture  Rows of GridDrawing's legend.
 */
function routeOn(array $picture, ?Heuristic $heuristic = null): Route
{
    return new AStarPathfinder($heuristic ?? new ManhattanDistance)->find(
        GridDrawing::grid($picture),
        GridDrawing::find($picture, 'A'),
        GridDrawing::find($picture, 'B'),
    );
}

it('follows a walkway from the start to the target', function (): void {
    expect(traceRoute([
        '#######',
        '#Aooo.#',
        '#...o.#',
        '#...oB#',
        '#######',
    ]))->toBe([
        '#######',
        '#****.#',
        '#...*.#',
        '#...**#',
        '#######',
    ]);
});

it('returns the start alone, with length 0, when the start is the target', function (): void {
    $grid = GridDrawing::grid(['.o.']);
    $cell = new GridCoordinate(0, 0);

    $route = new AStarPathfinder(new ManhattanDistance)->find($grid, $cell, $cell);

    expect($route->cells)->toEqual([$cell])
        ->and($route->length())->toBe(0);
});

it('finds the shortest of several walkways', function (): void {
    $route = routeOn([
        'oooooooo',
        'o######o',
        'o######o',
        'Aoo##ooB',
        '##o##o##',
        '##oooo##',
    ]);

    expect($route->length())->toBe(11);
});

it('goes around a wall', function (): void {
    expect(traceRoute([
        'ooooo',
        'o#Bo.',
        'o#...',
        'Ao...',
    ]))->toBe([
        '***oo',
        '*#*o.',
        '*#...',
        '*o...',
    ]);
});

it('goes around a partition', function (): void {
    expect(traceRoute([
        'oooo.',
        'oPPoo',
        'APPoB',
    ]))->toBe([
        '****.',
        '*PP**',
        '*PPo*',
    ]);
});

it('does not cross floor, sector outlines, walls or partitions that cut the walkway', function (string $blocker): void {
    $picture = ["Ao{$blocker}oB"];

    expect(fn (): Route => routeOn($picture))
        ->toThrow(RouteNotFound::class, 'No walkway leads from (0, 0) to (4, 0).');
})->with([
    'floor' => ['.'],
    'a sector outline' => ['S'],
    'a wall' => ['#'],
    'a partition' => ['P'],
]);

it('reports a target no walkway reaches', function (): void {
    expect(fn (): Route => routeOn([
        'Aoo#...',
        'ooo#.B.',
        '####...',
    ]))->toThrow(RouteNotFound::class, 'No walkway leads from (0, 0) to (5, 1).');
});

it('reports a target that is not a walkway, even next to the start', function (): void {
    $grid = GridDrawing::grid(['oS']);

    expect(fn (): Route => new AStarPathfinder(new ManhattanDistance)->find($grid, new GridCoordinate(0, 0), new GridCoordinate(1, 0)))
        ->toThrow(RouteNotFound::class, 'No walkway leads from (0, 0) to (1, 0).');
});

it('starts from any cell, since the shopper already stands there', function (): void {
    $grid = GridDrawing::grid(['Soo']);

    $route = new AStarPathfinder(new ManhattanDistance)->find($grid, new GridCoordinate(0, 0), new GridCoordinate(2, 0));

    expect($route->length())->toBe(2);
});

it('refuses a start or a target off the grid', function (int $startX, int $targetX): void {
    $grid = GridDrawing::grid(['ooo']);

    expect(fn (): Route => new AStarPathfinder(new ManhattanDistance)->find($grid, new GridCoordinate($startX, 0), new GridCoordinate($targetX, 0)))
        ->toThrow(CoordinateOutsideGrid::class, 'is outside the 3 x 1 grid.');
})->with([
    'start' => [3, 0],
    'target' => [0, 5],
]);

it('breaks ties between equally short routes the same way every time: right, left, down, up', function (): void {
    expect(traceRoute([
        'Aooo',
        'oooo',
        'oooB',
    ]))->toBe([
        '****',
        'ooo*',
        'ooo*',
    ]);
});

it('takes its heuristic from outside: with none, it is a plain shortest-path search', function (): void {
    $none = new class implements Heuristic
    {
        /**
         * Estimates nothing.
         */
        public function estimate(GridCoordinate $from, GridCoordinate $to): int
        {
            return 0;
        }
    };
    $picture = [
        'oooooooo',
        'o######o',
        'o######o',
        'Aoo##ooB',
        '##o##o##',
        '##oooo##',
    ];

    expect(routeOn($picture, $none)->length())->toBe(routeOn($picture)->length());
});
