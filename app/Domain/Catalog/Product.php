<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * An item of the catalogue that a user can put on a shopping list.
 */
final readonly class Product
{
    /**
     * Describes a product the catalogue holds.
     */
    public function __construct(
        public ProductId $id,
        public ProductName $name,
    ) {}
}
