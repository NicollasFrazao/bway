<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Grid\GridArea;

/**
 * A shelf or divider inside a store: a rectangle of the grid nobody can walk through.
 */
final readonly class Partition
{
    /**
     * Describes a partition of a store.
     */
    public function __construct(
        public PartitionId $id,
        public GridArea $area,
    ) {}
}
