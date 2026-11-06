<?php

declare(strict_types=1);

use App\Domain\Store\SectorId;
use App\Domain\Visit\DistanceTable;
use App\Domain\Visit\NearestNeighbourOrdering;

/**
 * The ids of the sectors, in order.
 *
 * @param  list<SectorId>  $sectors  Any sectors.
 * @return list<int>
 */
function idsOf(array $sectors): array
{
    return array_map(static fn (SectorId $sector): int => $sector->value, $sectors);
}

/**
 * A symmetric table from the distance of each unordered pair of sectors.
 *
 * @param  list<array{int, int, int}>  $pairs  Two sector ids and the steps between them, either way.
 */
function symmetricTable(array $pairs): DistanceTable
{
    $lengths = [];

    foreach ($pairs as [$a, $b, $length]) {
        $lengths[$a][$b] = $length;
        $lengths[$b][$a] = $length;
    }

    return new DistanceTable($lengths);
}

it('goes to the nearest stop not visited yet, from the start on', function (): void {
    $table = symmetricTable([[1, 2, 9], [1, 3, 2], [1, 4, 5], [2, 3, 8], [2, 4, 3], [3, 4, 4]]);

    $order = new NearestNeighbourOrdering()->order(new SectorId(1), sectorIds(2, 3, 4), $table);

    expect(idsOf($order))->toBe([3, 4, 2]);
});

it('breaks a tie toward the stop listed first, the lower sector id', function (): void {
    $table = symmetricTable([[1, 2, 3], [1, 3, 3], [2, 3, 1]]);

    $order = new NearestNeighbourOrdering()->order(new SectorId(1), sectorIds(2, 3), $table);

    expect(idsOf($order))->toBe([2, 3]);
});

it('takes the nearest stop by the route from where it is, even when it is longer the other way', function (): void {
    $table = new DistanceTable([1 => [2 => 6, 3 => 5], 2 => [3 => 1], 3 => [2 => 9]]);

    $order = new NearestNeighbourOrdering()->order(new SectorId(1), sectorIds(2, 3), $table);

    expect(idsOf($order))->toBe([3, 2]);
});

it('returns no stops for a visit without stops', function (): void {
    expect(new NearestNeighbourOrdering()->order(new SectorId(1), [], new DistanceTable([])))->toBe([]);
});
