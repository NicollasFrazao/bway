<?php

declare(strict_types=1);

namespace App\Application\Visit;

use App\Domain\Store\Store;
use App\Domain\Visit\VisitPlan;

/**
 * A planned visit with what a map of it needs: the store's layout and size, and each walk between
 * consecutive sectors.
 */
final readonly class VisitMap
{
    /**
     * Describes the map.
     *
     * @param  list<VisitLeg>  $legs  From the entrance to the first stop, then from stop to stop.
     */
    public function __construct(
        public Store $store,
        public VisitPlan $plan,
        public GridSize $size,
        public array $legs,
    ) {}
}
