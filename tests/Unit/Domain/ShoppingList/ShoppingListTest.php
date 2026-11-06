<?php

declare(strict_types=1);

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\DuplicateListItem;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ProductNotInList;
use App\Domain\ShoppingList\ShoppingList;

/**
 * A product of the catalogue with the given id and name.
 */
function product(int $id, string $name): Product
{
    return new Product(new ProductId($id), ProductName::from($name));
}

/**
 * The product ids on a list, in its order.
 *
 * @return list<int>
 */
function productIds(ShoppingList $list): array
{
    return array_map(fn (ListItem $item): int => $item->productId->value, $list->items());
}

it('starts empty and belongs to its owner', function (): void {
    $list = ShoppingList::empty(new UserId(7));

    expect($list->owner)->toEqual(new UserId(7))
        ->and($list->items())->toBe([]);
});

it('keeps the products added, in the order they were added, with their names', function (): void {
    $list = ShoppingList::empty(new UserId(1));

    $list->add(product(3, 'Rice'));
    $list->add(product(1, 'Beans'));

    expect(productIds($list))->toBe([3, 1])
        ->and(array_map(fn (ListItem $item): string => $item->productName->value, $list->items()))
        ->toBe(['Rice', 'Beans']);
});

it('refuses a product that is already on the list and keeps the list as it was', function (): void {
    $list = ShoppingList::empty(new UserId(1));
    $list->add(product(3, 'Rice'));

    expect(fn () => $list->add(product(3, 'Rice')))
        ->toThrow(DuplicateListItem::class, 'Product 3 is already on the shopping list.')
        ->and(productIds($list))->toBe([3]);
});

it('tells whether a product is on the list', function (): void {
    $list = ShoppingList::empty(new UserId(1));
    $list->add(product(3, 'Rice'));

    expect($list->contains(new ProductId(3)))->toBeTrue()
        ->and($list->contains(new ProductId(4)))->toBeFalse();
});

it('takes off a product the user no longer wants', function (): void {
    $list = ShoppingList::empty(new UserId(1));
    $list->add(product(3, 'Rice'));
    $list->add(product(1, 'Beans'));

    $list->remove(new ProductId(3));

    expect(productIds($list))->toBe([1]);
});

it('refuses to remove a product that is not on the list', function (): void {
    $list = ShoppingList::empty(new UserId(1));

    expect(fn () => $list->remove(new ProductId(9)))
        ->toThrow(ProductNotInList::class, 'Product 9 is not on the shopping list.');
});

it('removes an acquired item from the list and returns it', function (): void {
    $list = ShoppingList::empty(new UserId(1));
    $list->add(product(3, 'Rice'));
    $list->add(product(1, 'Beans'));

    $acquired = $list->acquire(new ProductId(3));

    expect($acquired)->toEqual(new ListItem(new ProductId(3), ProductName::from('Rice')))
        ->and(productIds($list))->toBe([1])
        ->and($list->contains(new ProductId(3)))->toBeFalse();
});

it('refuses to acquire a product twice', function (): void {
    $list = ShoppingList::empty(new UserId(1));
    $list->add(product(3, 'Rice'));
    $list->acquire(new ProductId(3));

    expect(fn (): ListItem => $list->acquire(new ProductId(3)))
        ->toThrow(ProductNotInList::class, 'Product 3 is not on the shopping list.');
});

it('lets an acquired product be added again, for the next visit', function (): void {
    $list = ShoppingList::empty(new UserId(1));
    $list->add(product(3, 'Rice'));
    $list->acquire(new ProductId(3));

    $list->add(product(3, 'Rice'));

    expect(productIds($list))->toBe([3]);
});

it('clears every item', function (): void {
    $list = ShoppingList::empty(new UserId(1));
    $list->add(product(3, 'Rice'));
    $list->add(product(1, 'Beans'));

    $list->clear();

    expect($list->items())->toBe([]);
});

it('restores a stored list in its order', function (): void {
    $rice = new ListItem(new ProductId(3), ProductName::from('Rice'));
    $beans = new ListItem(new ProductId(1), ProductName::from('Beans'));

    $list = ShoppingList::restore(new UserId(5), $rice, $beans);

    expect($list->owner)->toEqual(new UserId(5))
        ->and($list->items())->toBe([$rice, $beans]);
});

it('refuses to restore a list with the same product twice', function (): void {
    $rice = new ListItem(new ProductId(3), ProductName::from('Rice'));

    expect(fn (): ShoppingList => ShoppingList::restore(new UserId(5), $rice, $rice))
        ->toThrow(DuplicateListItem::class, 'Product 3 is already on the shopping list.');
});
