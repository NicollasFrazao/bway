<?php

declare(strict_types=1);

namespace App\Application\ShoppingList;

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductCatalog;

/**
 * The products of the catalogue whose name contains what the user typed (use case).
 */
final readonly class SearchProducts
{
    /**
     * Takes the catalogue.
     */
    public function __construct(private ProductCatalog $catalog) {}

    /**
     * The matching products by name; nothing for a blank term, rather than the whole catalogue.
     *
     * @return list<Product>
     */
    public function handle(string $term): array
    {
        $term = trim($term);

        return $term === '' ? [] : $this->catalog->search($term);
    }
}
