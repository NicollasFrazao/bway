<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Every product a user can add to a shopping list (port; implemented in Infrastructure).
 */
interface ProductCatalog
{
    /**
     * Finds one product by its identity.
     *
     * @throws ProductNotFound when the catalogue has no product with that id.
     */
    public function get(ProductId $id): Product;

    /**
     * Finds the products whose name contains the term, ignoring case, ordered by name.
     *
     * @return list<Product> Empty when nothing matches.
     */
    public function search(string $term): array;
}
