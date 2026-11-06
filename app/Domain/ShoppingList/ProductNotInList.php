<?php

declare(strict_types=1);

namespace App\Domain\ShoppingList;

use App\Domain\Catalog\ProductId;
use App\Domain\Shared\DomainError;

/**
 * A product was removed or acquired from a shopping list that does not have it.
 */
final class ProductNotInList extends DomainError
{
    /**
     * Builds the error for the missing product.
     */
    public static function forProduct(ProductId $productId): self
    {
        return new self(sprintf('Product %d is not on the shopping list.', $productId->value));
    }
}
