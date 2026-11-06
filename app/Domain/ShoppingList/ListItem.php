<?php

declare(strict_types=1);

namespace App\Domain\ShoppingList;

use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;

/**
 * One product on a shopping list.
 *
 * It refers to the product by id, as one aggregate refers to another, and keeps the name the
 * product had when it was added so the list can be shown without asking the catalogue.
 */
final readonly class ListItem
{
    /**
     * Describes the item of one product.
     */
    public function __construct(
        public ProductId $productId,
        public ProductName $productName,
    ) {}
}
