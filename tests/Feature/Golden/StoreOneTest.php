<?php

declare(strict_types=1);

use App\Application\FloorPlan\MapFloorPlan;
use App\Application\Routing\FindRouteBetweenSectors;
use App\Domain\Grid\CellKind;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Routing\Route;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\V1StoreOne;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

/*
 * Golden tests: the PHP mapping and routing against what v1 computed for store 1 and committed at
 * the `v1` tag (tests/Fixtures/v1/store-1/). Every difference is listed here with its cause, and
 * any other difference fails. The decisions are in docs/adr/0003-floor-plan-mapping-and-routing.md.
 */
uses(RefreshDatabase::class);

/**
 * The ordered pairs where v2 deliberately does not return the route v1 stored, and why.
 */
const V1_ROUTE_DIFFERENCES = [
    '10-2' => 'v1 was not the shortest',
    '11-2' => 'v1 was not the shortest',
    '6-2' => 'v1 was not the shortest',
    '5-10' => 'an equally short route',
    '2-5' => 'v1 stored it from an earlier floor plan',
];

/**
 * The row and the column v1's mapping got wrong (both 477: the slip compared y with the width).
 */
const V1_SLIP = 477;

/**
 * The route PHP finds on the grid it mapped, between the centres of two sectors of store 1.
 */
function phpRoute(int $from, int $to): Route
{
    return new AStarPathfinder(new ManhattanDistance)->find(
        V1StoreOne::mappedByPhp(),
        V1StoreOne::sectorArea($from)->center(),
        V1StoreOne::sectorArea($to)->center(),
    );
}

/**
 * Cells as [x, y] pairs, which read better than objects in a failed comparison.
 *
 * @param  list<GridCoordinate>  $cells  Any cells.
 * @return list<array{int, int}>
 */
function pairs(array $cells): array
{
    return array_map(static fn (GridCoordinate $cell): array => [$cell->x, $cell->y], $cells);
}

/**
 * A grid of codes without v1's slipped row and column.
 *
 * @param  list<list<int>>  $codes  Rows of cell codes.
 * @return list<list<int>>
 */
function withoutSlips(array $codes): array
{
    $rows = array_values(array_diff_key($codes, [V1_SLIP => true]));

    return array_map(static fn (array $row): array => array_values(array_diff_key($row, [V1_SLIP => true])), $rows);
}

/**
 * The ordered pairs of different sectors of store 1, with or without the listed differences.
 *
 * @return Generator<string, array{int, int}>
 */
function sectorPairs(bool $withDifferences): Generator
{
    foreach (V1StoreOne::SECTOR_IDS as $from) {
        foreach (array_diff(V1StoreOne::SECTOR_IDS, [$from]) as $to) {
            if (
                ! $withDifferences
                && isset(V1_ROUTE_DIFFERENCES["{$from}-{$to}"])
            ) {
                continue;
            }

            yield "{$from} to {$to}" => [$from, $to];
        }
    }
}

dataset('pairs of different sectors of store 1', fn (): Generator => sectorPairs(withDifferences: true));

dataset('pairs where v2 keeps the route v1 stored', fn (): Generator => sectorPairs(withDifferences: false));

it('maps v1 floor plan of store 1 to the grid v1 made, cell for cell, apart from the two v1 slips', function (): void {
    $grid = V1StoreOne::mappedByPhp();
    $php = array_map(
        static fn (array $row): array => array_map(static fn (CellKind $kind): int => $kind->value, $row),
        $grid->rows(),
    );

    expect([$grid->width, $grid->height])->toBe([478, 492])
        ->and(count(V1StoreOne::grid()))->toBe(492)
        ->and(withoutSlips($php))->toBe(withoutSlips(V1StoreOne::grid()));
});

it('closes the right-hand wall, which v1 left as floor', function (): void {
    $php = array_column(V1StoreOne::mappedByPhp()->rows(), V1_SLIP);
    $v1 = array_column(V1StoreOne::grid(), V1_SLIP);

    expect(array_unique($php, SORT_REGULAR))->toBe([CellKind::Wall])
        ->and(array_count_values($v1))->toBe([1 => 3, 0 => 489]);
});

it('keeps row 477 as the plan draws it, where v1 drew a wall across', function (): void {
    $php = V1StoreOne::mappedByPhp()->rows()[V1_SLIP] ?? [];
    $v1 = V1StoreOne::grid()[V1_SLIP] ?? [];

    expect(array_unique($v1))->toBe([1])
        ->and(array_keys($php, CellKind::Wall, true))->toBe([0, 477]);
});

it('fits the sectors and partitions v1 mapped, none overlapping, inside the PHP grid', function (): void {
    expect(function (): void {
        V1StoreOne::store()->ensureFitsInside(V1StoreOne::mappedByPhp());
    })->not->toThrow(Throwable::class);
});

it('starts and ends each v1 route at the centres of its sectors, which proves the sector ids', function (int $from, int $to): void {
    $route = V1StoreOne::route($from, $to);
    $ends = [...array_slice($route, 0, 1), ...array_slice($route, -1)];

    expect(pairs($ends))->toBe(pairs([V1StoreOne::sectorArea($from)->center(), V1StoreOne::sectorArea($to)->center()]));
})->with('pairs of different sectors of store 1');

it('finds the very route v1 stored', function (int $from, int $to): void {
    expect(pairs(phpRoute($from, $to)->cells))->toBe(pairs(V1StoreOne::route($from, $to)));
})->with('pairs where v2 keeps the route v1 stored');

it('stays on a sector it is already in, with length 0, where v1 stored an empty route', function (int $sector): void {
    expect(V1StoreOne::route($sector, $sector))->toBe([])
        ->and(phpRoute($sector, $sector)->length())->toBe(0);
})->with(V1StoreOne::SECTOR_IDS);

it('finds a shorter route than v1 to sector 2 from sectors 10, 11 and 6, as short as v1 found the other way', function (int $from, int $v1Length, int $length): void {
    expect(count(V1StoreOne::route($from, 2)) - 1)->toBe($v1Length)
        ->and(phpRoute($from, 2)->length())->toBe($length)
        ->and(count(V1StoreOne::route(2, $from)) - 1)->toBe($length);
})->with([
    'from 10' => [10, 554, 528],
    'from 11' => [11, 835, 809],
    'from 6' => [6, 262, 236],
]);

it('finds a route as short as v1 from sector 5 to sector 10, along other cells', function (): void {
    $route = phpRoute(5, 10);

    expect($route->length())->toBe(515)
        ->and(count(V1StoreOne::route(5, 10)) - 1)->toBe(515)
        ->and(pairs($route->cells))->not->toBe(pairs(V1StoreOne::route(5, 10)));
});

it('finds a route as short as v1 from sector 2 to sector 5, where v1 stored cells its own grid has no walkway on', function (): void {
    $offWalkway = array_filter(
        V1StoreOne::route(2, 5),
        static fn (GridCoordinate $cell): bool => V1StoreOne::codeAt($cell) !== CellKind::Walkway->value,
    );

    expect(count($offWalkway))->toBe(152)
        ->and(phpRoute(2, 5)->length())->toBe(389)
        ->and(count(V1StoreOne::route(2, 5)) - 1)->toBe(389);
});

it('maps the plan and serves the routes of store 1 through the use cases, keeping both in MySQL', function (): void {
    new StoreWriter()->store(V1StoreOne::store());

    app(MapFloorPlan::class)->handle(new StoreId(1), V1StoreOne::floorPlan());
    $found = app(FindRouteBetweenSectors::class)->handle(new StoreId(1), new SectorId(3), new SectorId(6));
    $kept = app(FindRouteBetweenSectors::class)->handle(new StoreId(1), new SectorId(3), new SectorId(6));

    expect(pairs($found->cells))->toBe(pairs(V1StoreOne::route(3, 6)))
        ->and($kept)->toEqual($found);
    assertDatabaseHas('store_grids', ['store_id' => 1, 'width' => 478, 'height' => 492]);
    assertDatabaseHas('routes', ['store_id' => 1, 'from_sector_id' => 3, 'to_sector_id' => 6]);

    app(MapFloorPlan::class)->handle(new StoreId(1), V1StoreOne::floorPlan());

    assertDatabaseMissing('routes', ['store_id' => 1]);
});
