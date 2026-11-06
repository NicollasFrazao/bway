<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\Store\SectorId;
use LogicException;

/**
 * How the visit planner orders the sectors to visit (strategy).
 */
interface VisitOrderingStrategy
{
    /**
     * Orders the stops of a visit that starts at the start sector.
     *
     * @param  list<SectorId>  $stops  The sectors to visit, by ascending id, the start not among them.
     * @return list<SectorId> Every stop exactly once, in visiting order; deterministic for the same
     *                        input.
     *
     * @throws LogicException when the strategy asks the table for a pair it does not hold.
     */
    public function order(SectorId $start, array $stops, DistanceTable $distances): array;
}
