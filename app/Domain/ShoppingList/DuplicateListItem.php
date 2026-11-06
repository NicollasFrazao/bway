<?php

declare(strict_types=1);

namespace App\Domain\ShoppingList;

use App\Domain\Catalog\ProductId;
use App\Domain\Shared\DomainError;

/**
 * A product was put on a shopping list that already has it.
 */
final class DuplicateListItem extends DomainError
{
    /**
     * Builds the error for the repeated product.
     */
    public static function forProduct(ProductId $productId): self
    {
        return new self(sprintf('Product %d is already on the shopping list.', $productId->value));
    }
}
