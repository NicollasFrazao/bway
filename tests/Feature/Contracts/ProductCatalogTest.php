<?php

declare(strict_types=1);

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Catalog\ProductNotFound;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\EloquentProductCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Domain\InMemoryProductCatalog;

uses(RefreshDatabase::class);

/**
 * The four products every catalogue of the contract holds.
 *
 * @return list<Product>
 */
function catalogueOfFour(): array
{
    return [
        new Product(new ProductId(1), ProductName::from('Rice')),
        new Product(new ProductId(2), ProductName::from('Brown rice')),
        new Product(new ProductId(3), ProductName::from('Beans')),
        new Product(new ProductId(4), ProductName::from('Rice flour')),
    ];
}

/*
 * The contract every ProductCatalog keeps, given four products. The in-memory adapter is the
 * reference; the Eloquent adapter runs it on MySQL.
 */
dataset('product catalogues', [
    'in memory' => fn (): ProductCatalog => new InMemoryProductCatalog(...catalogueOfFour()),
    'eloquent' => function (): ProductCatalog {
        foreach (catalogueOfFour() as $product) {
            ProductRecord::query()->forceCreate(['id' => $product->id->value, 'name' => $product->name->value]);
        }

        return new EloquentProductCatalog;
    },
]);

it('finds a product by id', function (ProductCatalog $catalog): void {
    expect($catalog->get(new ProductId(3)))->toEqual(new Product(new ProductId(3), ProductName::from('Beans')));
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

it('matches the wildcards of SQL as plain characters', function (ProductCatalog $catalog, string $term): void {
    expect($catalog->search($term))->toBe([]);
})->with('product catalogues')->with(['%', '_', 'R%e', 'Ric_', '\\']);
