<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Catalog\ProductNotFound;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;

/**
 * The product catalogue kept in the `products` table.
 */
final readonly class EloquentProductCatalog implements ProductCatalog
{
    /**
     * Loads the product with that id.
     *
     * @throws ProductNotFound when no row has that id.
     */
    public function get(ProductId $id): Product
    {
        $record = ProductRecord::query()->find($id->value) ?? throw ProductNotFound::withId($id);

        return $this->product($record);
    }

    /**
     * The products whose name contains the term, ignoring case (the column's collation), ordered by
     * name; `%`, `_` and `\` in the term are matched as themselves.
     *
     * @return list<Product>
     */
    public function search(string $term): array
    {
        $pattern = '%'.addcslashes($term, '\\%_').'%';

        return array_values(
            ProductRecord::query()
                ->whereLike('name', $pattern)
                ->orderBy('name')
                ->orderBy('id')
                ->get()
                ->map($this->product(...))
                ->all(),
        );
    }

    /**
     * The domain product a row stands for.
     */
    private function product(ProductRecord $record): Product
    {
        return new Product(new ProductId($record->id), ProductName::from($record->name));
    }
}
