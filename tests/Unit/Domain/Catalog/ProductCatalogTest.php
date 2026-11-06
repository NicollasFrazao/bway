<?php

declare(strict_types=1);

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Catalog\ProductNotFound;
use Tests\Support\Domain\InMemoryProductCatalog;

/*
 * The contract every ProductCatalog keeps, given four products. The in-memory adapter is the
 * reference; the Eloquent adapter joins the dataset when it exists.
 * TODO(#7): run this contract against the Eloquent catalogue on MySQL.
 */
dataset('product catalogues', [
    'in memory' => fn (): ProductCatalog => new InMemoryProductCatalog(
        new Product(new ProductId(1), ProductName::from('Rice')),
        new Product(new ProductId(2), ProductName::from('Brown rice')),
        new Product(new ProductId(3), ProductName::from('Beans')),
        new Product(new ProductId(4), ProductName::from('Rice flour')),
    ),
]);

it('finds a product by id', function (ProductCatalog $catalog): void {
    expect($catalog->get(new ProductId(3))->name->value)->toBe('Beans');
})->with('product catalogues');

it('reports a product it does not have', function (ProductCatalog $catalog): void {
    expect(fn (): Product => $catalog->get(new ProductId(9)))
        ->toThrow(ProductNotFound::class, 'The catalogue has no product 9.');
})->with('product catalogues');

it('searches names by a part of them, ignoring case, ordered by name', function (ProductCatalog $catalog): void {
    $names = array_map(fn (Product $product): string => $product->name->value, $catalog->search('RICE'));

    expect($names)->toBe(['Brown rice', 'Rice', 'Rice flour']);
})->with('product catalogues');

it('finds nothing for a term no name contains', function (ProductCatalog $catalog): void {
    expect($catalog->search('coffee'))->toBe([]);
})->with('product catalogues');
