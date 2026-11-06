<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\Store\SectorId;

/**
 * Orders a visit greedily: from the start, always the nearest stop not visited yet (v1's ordering).
 *
 * A tie goes to the stop listed first, the lower sector id. Fast and usually good, but not always
 * the shortest walk: going to the nearest stop first can leave a long walk back for the last ones.
 */
final readonly class NearestNeighbourOrdering implements VisitOrderingStrategy
{
    /**
     * Picks the nearest remaining stop until none is left.
     *
     * @param  list<SectorId>  $stops  The sectors to visit, by ascending id.
     * @return list<SectorId>
     */
    public function order(SectorId $start, array $stops, DistanceTable $distances): array
    {
        $order = [];
        $current = $start;
        $remaining = $stops;

        while ($remaining !== []) {
            $current = $this->nearest($current, $remaining, $distances);
            $order[] = $current;
            $remaining = array_values(array_filter(
                $remaining,
                static fn (SectorId $stop): bool => $stop->value !== $current->value,
            ));
        }

        return $order;
    }

    /**
     * The candidate the shortest route from the sector leads to; the first listed on a tie.
     *
     * @param  non-empty-list<SectorId>  $candidates  The stops not visited yet.
     */
    private function nearest(SectorId $from, array $candidates, DistanceTable $distances): SectorId
    {
        $nearest = $candidates[0];
        $shortest = $distances->between($from, $nearest);

        foreach (array_slice($candidates, 1) as $candidate) {
            $length = $distances->between($from, $candidate);

            if ($length >= $shortest) {
                continue;
            }

            $nearest = $candidate;
            $shortest = $length;
        }

        return $nearest;
    }
}
