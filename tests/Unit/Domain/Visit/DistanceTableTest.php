<?php

declare(strict_types=1);

use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use App\Domain\Visit\DistanceTable;
use Tests\Support\Domain\FixedRouteLengths;

/**
 * Sector ids, as the strategies take them.
 *
 * @return list<SectorId>
 */
function sectorIds(int ...$ids): array
{
    return array_map(static fn (int $id): SectorId => new SectorId($id), array_values($ids));
}

it('measures from the start and from every stop to every other stop, never back to the start', function (): void {
    $lengths = new FixedRouteLengths([1 => [2 => 4, 3 => 6], 2 => [3 => 5], 3 => [2 => 7]]);

    $table = DistanceTable::measure($lengths, new StoreId(1), new SectorId(1), sectorIds(2, 3));

    expect($lengths->asked)->toBe(['1-2', '1-3', '2-3', '3-2'])
        ->and($table->between(new SectorId(1), new SectorId(3)))->toBe(6)
        ->and($table->between(new SectorId(3), new SectorId(2)))->toBe(7);
});

it('measures routes to the start when the start is a stop too', function (): void {
    $lengths = new FixedRouteLengths([1 => [2 => 4], 2 => [1 => 4]]);

    DistanceTable::measure($lengths, new StoreId(1), new SectorId(1), sectorIds(1, 2));

    expect($lengths->asked)->toBe(['1-2', '2-1']);
});

it('measures nothing for a visit without stops', function (): void {
    $lengths = new FixedRouteLengths([]);

    $table = DistanceTable::measure($lengths, new StoreId(1), new SectorId(1), []);

    expect($lengths->asked)->toBe([])
        ->and($table->through(sectorIds(1)))->toBe(0);
});

it('is 0 from a sector to itself, without measuring it', function (): void {
    expect(new DistanceTable([])->between(new SectorId(4), new SectorId(4)))->toBe(0);
});

it('adds up the legs of a walk', function (): void {
    $table = new DistanceTable([1 => [2 => 4], 2 => [3 => 5], 3 => [2 => 7]]);

    expect($table->through(sectorIds(1, 2, 3)))->toBe(9)
        ->and($table->through(sectorIds(1, 2, 3, 2)))->toBe(16)
        ->and($table->through([]))->toBe(0);
});

it('refuses a pair it did not measure', function (): void {
    expect(fn (): int => new DistanceTable([1 => [2 => 4]])->between(new SectorId(2), new SectorId(1)))
        ->toThrow(LogicException::class, 'The route from sector 2 to sector 1 was not measured.');
});
