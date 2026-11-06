<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductNotFound;

/**
 * Serves a fixed set of products from memory.
 */
final readonly class InMemoryProductCatalog implements ProductCatalog
{
    /**
     * The products, in the order given.
     *
     * @var list<Product>
     */
    private array $products;

    /**
     * Holds the products the tests need.
     */
    public function __construct(Product ...$products)
    {
        $this->products = array_values($products);
    }

    /**
     * Finds the product with that id.
     *
     * @throws ProductNotFound when none of the products has it.
     */
    public function get(ProductId $id): Product
    {
        foreach ($this->products as $product) {
            if ($product->id->value === $id->value) {
                return $product;
            }
        }

        throw ProductNotFound::withId($id);
    }

    /**
     * The products whose name contains the term, ignoring case, ordered by name.
     *
     * @return list<Product>
     */
    public function search(string $term): array
    {
        $found = array_filter(
            $this->products,
            static fn (Product $product): bool => mb_stripos($product->name->value, $term) !== false,
        );
        usort(
            $found,
            static fn (Product $a, Product $b): int => strcmp(mb_strtolower($a->name->value), mb_strtolower($b->name->value)),
        );

        return $found;
    }
}
