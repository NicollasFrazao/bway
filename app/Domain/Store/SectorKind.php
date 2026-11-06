<?php

declare(strict_types=1);

namespace App\Domain\Store;

/**
 * What a sector is for: the place a visit starts, or a place where products are sold.
 */
enum SectorKind
{
    /**
     * Where every visit starts; a store has exactly one.
     */
    case Entrance;

    /**
     * Where a group of products is sold.
     */
    case Sales;
}
