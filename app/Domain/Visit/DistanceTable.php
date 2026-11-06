<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\Routing\RouteNotFound;
use App\Domain\Shared\DomainError;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use LogicException;

/**
 * The route lengths an ordering strategy may use for one visit: from the start and from every stop
 * to every other stop, measured before ordering, so a strategy is a pure computation.
 *
 * Routes back to the start are not measured unless the start is a stop too: a visit does not end
 * where it started.
 */
final readonly class DistanceTable
{
    /**
     * Holds lengths already measured.
     *
     * @param  array<int, array<int, int>>  $lengths  Steps, keyed by the id of the sector walked
     *                                                from, then by the id of the sector walked to.
     */
    public function __construct(private array $lengths) {}

    /**
     * Measures every route a visit from the start through the stops may take.
     *
     * @param  list<SectorId>  $stops  The sectors to visit, the start not among them.
     *
     * @throws RouteNotFound when a stop cannot be reached from the start or from another stop.
     * @throws DomainError when the store, a sector or the store's grid cannot be found.
     */
    public static function measure(RouteLengths $routeLengths, StoreId $storeId, SectorId $start, array $stops): self
    {
        $lengths = [];
        $origins = [$start->value => $start];

        foreach ($stops as $stop) {
            $origins[$stop->value] = $stop;
        }

        foreach ($origins as $from) {
            foreach ($stops as $to) {
                if ($from->value === $to->value) {
                    continue;
                }

                $lengths[$from->value][$to->value] = $routeLengths->between($storeId, $from, $to);
            }
        }

        return new self($lengths);
    }

    /**
     * The steps from one sector to another; 0 from a sector to itself, which is not measured (the
     * entrance is a stop too when it sells something on the list).
     *
     * @throws LogicException when the pair was not measured: an ordering strategy asked for a route
     *                        the visit does not take.
     */
    public function between(SectorId $from, SectorId $to): int
    {
        if ($from->value === $to->value) {
            return 0;
        }

        return $this->lengths[$from->value][$to->value]
            ?? throw new LogicException(sprintf('The route from sector %d to sector %d was not measured.', $from->value, $to->value));
    }

    /**
     * The steps of a walk through the sectors in the order given; 0 for one sector or none.
     *
     * @param  list<SectorId>  $sectors  The start first, then each stop in visiting order.
     *
     * @throws LogicException when a leg of the walk was not measured.
     */
    public function through(array $sectors): int
    {
        if ($sectors === []) {
            return 0;
        }

        $length = 0;
        $from = $sectors[0];

        foreach (array_slice($sectors, 1) as $to) {
            $length += $this->between($from, $to);
            $from = $to;
        }

        return $length;
    }
}
