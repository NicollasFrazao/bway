<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Catalog\ProductId;

/**
 * The fact that a store sells a product in one of its sectors.
 */
final readonly class SectorProduct
{
    /**
     * Places a product of the catalogue in a sector.
     */
    public function __construct(
        public SectorId $sectorId,
        public ProductId $productId,
    ) {}
}
