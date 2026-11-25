<?php

declare(strict_types=1);

namespace App\Application\Visit;

use App\Domain\Grid\GridCoordinate;
use App\Domain\Store\Sector;

/**
 * One walk of a visit, from one sector to the next, as the map draws it.
 */
final readonly class VisitLeg
{
    /**
     * Describes the walk.
     *
     * @param  non-empty-list<GridCoordinate>  $corners  The cells where the walk starts, turns and ends; the
     *                                                   straight runs between them are left out.
     * @param  int  $length  The steps of the walk.
     */
    public function __construct(
        public Sector $from,
        public Sector $to,
        public array $corners,
        public int $length,
    ) {}
}
