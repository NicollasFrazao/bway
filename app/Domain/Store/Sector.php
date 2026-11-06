<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Grid\GridArea;
use App\Domain\Shared\Name;

/**
 * An area of a store, a rectangle on its grid, where a visit starts or a group of products is sold.
 */
final readonly class Sector
{
    /**
     * Describes a sector of a store.
     */
    public function __construct(
        public SectorId $id,
        public Name $name,
        public GridArea $area,
        public SectorKind $kind,
    ) {}

    /**
     * Tells whether visits start here.
     */
    public function isEntrance(): bool
    {
        return $this->kind === SectorKind::Entrance;
    }
}
