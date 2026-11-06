<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\ShoppingList\ListItem;
use App\Domain\Store\Sector;

/**
 * A visit through a store, planned for a shopping list: where it starts, the sectors to walk
 * through in order, and the items the store does not sell.
 *
 * The plan is computed, not stored: acquiring an item takes it off the list, and the next plan no
 * longer has it.
 */
final readonly class VisitPlan
{
    /**
     * Describes the planned visit.
     *
     * @param  list<VisitStop>  $stops  In visiting order; empty when the store sells nothing on the list.
     * @param  list<ListItem>  $unsold  The items no sector of the store sells, in list order; v1
     *                                  dropped them without a word.
     * @param  int  $length  The steps from the start through every stop, in order.
     */
    public function __construct(
        public Sector $start,
        public array $stops,
        public array $unsold,
        public int $length,
    ) {}
}
