<?php

declare(strict_types=1);

use App\Domain\Store\SectorId;
use App\Domain\Visit\DistanceTable;
use App\Domain\Visit\NearestNeighbourOrdering;
use App\Domain\Visit\TwoOptOrdering;
use App\Domain\Visit\VisitOrderingStrategy;

/**
 * A strategy that always proposes the stops in the order given, by ascending id.
 */
function asListed(): VisitOrderingStrategy
{
    return new class implements VisitOrderingStrategy
    {
        /**
         * The stops as they came.
         *
         * @param  list<SectorId>  $stops  The sectors to visit.
         * @return list<SectorId>
         */
        public function order(SectorId $start, array $stops, DistanceTable $distances): array
        {
            return $stops;
        }
    };
}

/**
 * Sectors on a line, keyed by id: the distance between two is the difference of their positions.
 *
 * @param  array<int, int>  $positions  The position of each sector.
 */
function lineTable(array $positions): DistanceTable
{
    $lengths = [];

    foreach ($positions as $from => $a) {
        foreach ($positions as $to => $b) {
            $lengths[$from][$to] = abs($a - $b);
        }
    }

    return new DistanceTable($lengths);
}

it('reverses the first two stops nearest neighbour chose, when going to the farther one first is shorter', function (): void {
    // Entrance 1 at 0, stops 2 at +1, 3 at -2, 4 at +4: nearest neighbour walks 1 + 3 + 6 = 10.
    $table = lineTable([1 => 0, 2 => 1, 3 => -2, 4 => 4]);
    $stops = sectorIds(2, 3, 4);

    $nearest = new NearestNeighbourOrdering()->order(new SectorId(1), $stops, $table);
    $improved = new TwoOptOrdering(new NearestNeighbourOrdering)->order(new SectorId(1), $stops, $table);

    expect(idsOf($nearest))->toBe([2, 3, 4])
        ->and($table->through(sectorIds(1, ...idsOf($nearest))))->toBe(10)
        ->and(idsOf($improved))->toBe([3, 2, 4])
        ->and($table->through(sectorIds(1, ...idsOf($improved))))->toBe(8);
});

it('keeps reversing until no reversal shortens the walk', function (): void {
    // Listed 2, 3, 4, 5 on a line at 4, 1, 3, 2 from the start at 0: 4 + 3 + 2 + 1 = 10 steps.
    $table = lineTable([1 => 0, 2 => 4, 3 => 1, 4 => 3, 5 => 2]);

    $order = new TwoOptOrdering(asListed())->order(new SectorId(1), sectorIds(2, 3, 4, 5), $table);

    expect(idsOf($order))->toBe([3, 5, 4, 2])
        ->and($table->through(sectorIds(1, ...idsOf($order))))->toBe(4);
});

it('keeps an order no reversal shortens', function (): void {
    $table = lineTable([1 => 0, 2 => 1, 3 => 2, 4 => 3]);

    $order = new TwoOptOrdering(asListed())->order(new SectorId(1), sectorIds(2, 3, 4), $table);

    expect(idsOf($order))->toBe([2, 3, 4]);
});

it('measures the whole walk, so a run is reversed only if its routes are shorter the other way too', function (): void {
    // 2 then 3 is 1 + 1, but 3 back to 2 is 50: reversing [2, 3] would not pay.
    $table = new DistanceTable([1 => [2 => 1, 3 => 2], 2 => [3 => 1], 3 => [2 => 50]]);

    $order = new TwoOptOrdering(asListed())->order(new SectorId(1), sectorIds(2, 3), $table);

    expect(idsOf($order))->toBe([2, 3]);
});

it('is never longer than the order it improves', function (int ...$stopPositions): void {
    $positions = [1 => 0];

    foreach (array_values($stopPositions) as $index => $position) {
        $positions[$index + 2] = $position;
    }

    $stops = sectorIds(...range(2, count($stopPositions) + 1));
    $table = lineTable($positions);

    $proposed = $table->through([new SectorId(1), ...new NearestNeighbourOrdering()->order(new SectorId(1), $stops, $table)]);
    $improved = $table->through([new SectorId(1), ...new TwoOptOrdering(new NearestNeighbourOrdering)->order(new SectorId(1), $stops, $table)]);

    expect($improved)->toBeLessThanOrEqual($proposed);
})->with([
    'one stop' => [5],
    'zigzag' => [1, -2, 3, -4, 5],
    'one side' => [3, 1, 2],
]);

it('returns no stops for a visit without stops', function (): void {
    expect(new TwoOptOrdering(asListed())->order(new SectorId(1), [], new DistanceTable([])))->toBe([]);
});
