<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Catalog\ProductId;
use App\Domain\Shared\DomainError;

/**
 * An assortment placed one product in more than one sector of a store.
 */
final class ProductInSeveralSectors extends DomainError
{
    /**
     * Builds the error for the product and the two sectors it was placed in.
     */
    public static function inStore(StoreId $storeId, ProductId $productId, SectorId $first, SectorId $second): self
    {
        return new self(sprintf(
            'Store %d places product %d in sector %d and again in sector %d; a product is sold in one sector of a store.',
            $storeId->value,
            $productId->value,
            $first->value,
            $second->value,
        ));
    }
}
