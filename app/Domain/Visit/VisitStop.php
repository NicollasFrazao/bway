<?php

declare(strict_types=1);

namespace App\Domain\Visit;

use App\Domain\ShoppingList\ListItem;
use App\Domain\Store\Sector;

/**
 * One sector of a visit and the items of the list the shopper picks up there.
 */
final readonly class VisitStop
{
    /**
     * Describes a stop.
     *
     * @param  non-empty-list<ListItem>  $items  In the order they were added to the list.
     */
    public function __construct(
        public Sector $sector,
        public array $items,
    ) {}
}
