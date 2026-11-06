<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\Store\SectorId;

/**
 * Improves the order another strategy proposes by 2-opt: while reversing a run of consecutive stops
 * shortens the walk, reverse it.
 *
 * The walk starts at the start and ends at the last stop, so only the stops move. Each reversal
 * makes the walk strictly shorter, so it stops; the result is never longer than the proposal, though
 * it is a local optimum, not always the shortest walk of all. The whole walk is measured for each
 * candidate, so routes need not be as long one way as the other.
 */
final readonly class TwoOptOrdering implements VisitOrderingStrategy
{
    /**
     * Takes the strategy whose order is improved, usually nearest neighbour.
     */
    public function __construct(private VisitOrderingStrategy $proposal) {}

    /**
     * Orders the stops with the proposal, then reverses runs while that shortens the walk.
     *
     * @param  list<SectorId>  $stops  The sectors to visit, by ascending id.
     * @return list<SectorId>
     */
    public function order(SectorId $start, array $stops, DistanceTable $distances): array
    {
        $order = $this->proposal->order($start, $stops, $distances);
        $shorter = $this->firstShorterReversal($start, $order, $distances);

        while ($shorter !== null) {
            $order = $shorter;
            $shorter = $this->firstShorterReversal($start, $order, $distances);
        }

        return $order;
    }

    /**
     * The order with one run of stops reversed that is shorter to walk, trying runs by where they
     * begin and then by their length; null when no reversal helps.
     *
     * @param  list<SectorId>  $order  The stops in visiting order.
     * @return list<SectorId>|null
     */
    private function firstShorterReversal(SectorId $start, array $order, DistanceTable $distances): ?array
    {
        $length = $distances->through([$start, ...$order]);
        $count = count($order);

        for ($first = 0; $first < $count - 1; $first++) {
            for ($last = $first + 1; $last < $count; $last++) {
                $candidate = $this->reversed($order, $first, $last);

                if ($distances->through([$start, ...$candidate]) < $length) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * The order with the stops from the first to the last position, both included, reversed.
     *
     * @param  list<SectorId>  $order  The stops in visiting order.
     * @return list<SectorId>
     */
    private function reversed(array $order, int $first, int $last): array
    {
        return [
            ...array_slice($order, 0, $first),
            ...array_reverse(array_slice($order, $first, $last - $first + 1)),
            ...array_slice($order, $last + 1),
        ];
    }
}
