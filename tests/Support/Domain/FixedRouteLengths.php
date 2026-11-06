<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use App\Domain\Visit\RouteLengths;
use LogicException;

/**
 * Route lengths from a hand-made table, for tests of the planner that care about the order, not the
 * grid; it records each pair it is asked for.
 */
final class FixedRouteLengths implements RouteLengths
{
    /**
     * The pairs asked for, as "from-to", in the order asked.
     *
     * @var list<string>
     */
    public private(set) array $asked = [];

    /**
     * Holds the table.
     *
     * @param  array<int, array<int, int>>  $lengths  Steps, keyed by the sector walked from, then
     *                                                by the sector walked to.
     */
    public function __construct(private readonly array $lengths) {}

    /**
     * The length the table holds for the pair.
     *
     * @throws LogicException when the table has no length for the pair.
     */
    public function between(StoreId $storeId, SectorId $from, SectorId $to): int
    {
        $this->asked[] = "{$from->value}-{$to->value}";

        return $this->lengths[$from->value][$to->value]
            ?? throw new LogicException("The table has no length from {$from->value} to {$to->value}.");
    }
}
