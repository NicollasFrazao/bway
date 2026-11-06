<?php

declare(strict_types=1);

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\ShoppingList\ShoppingListRepository;
use Tests\Support\Domain\InMemoryShoppingListRepository;

/*
 * The contract every ShoppingListRepository keeps. The in-memory adapter is the reference; the
 * Eloquent adapter joins the dataset when it exists.
 * TODO(#7): run this contract against the Eloquent repository on MySQL.
 */
dataset('shopping list repositories', [
    'in memory' => fn (): ShoppingListRepository => new InMemoryShoppingListRepository,
]);

it('gives an empty list to a user who has never saved one', function (ShoppingListRepository $repository): void {
    $list = $repository->forOwner(new UserId(4));

    expect($list->owner)->toEqual(new UserId(4))
        ->and($list->items())->toBe([]);
})->with('shopping list repositories');

it('gives back the items saved, in their order', function (ShoppingListRepository $repository): void {
    $list = ShoppingList::empty(new UserId(4));
    $list->add(new Product(new ProductId(3), ProductName::from('Rice')));
    $list->add(new Product(new ProductId(1), ProductName::from('Beans')));

    $repository->save($list);

    expect($repository->forOwner(new UserId(4))->items())->toEqual($list->items());
})->with('shopping list repositories');

it('keeps only what was saved, not later changes to the same object', function (ShoppingListRepository $repository): void {
    $list = ShoppingList::empty(new UserId(4));
    $list->add(new Product(new ProductId(3), ProductName::from('Rice')));
    $repository->save($list);

    $list->acquire(new ProductId(3));

    expect($repository->forOwner(new UserId(4))->contains(new ProductId(3)))->toBeTrue();
})->with('shopping list repositories');

it('replaces the list of the owner on each save and keeps owners apart', function (ShoppingListRepository $repository): void {
    $first = ShoppingList::empty(new UserId(4));
    $first->add(new Product(new ProductId(3), ProductName::from('Rice')));
    $repository->save($first);
    $first->clear();
    $repository->save($first);

    $other = ShoppingList::empty(new UserId(5));
    $other->add(new Product(new ProductId(1), ProductName::from('Beans')));
    $repository->save($other);

    expect($repository->forOwner(new UserId(4))->items())->toBe([])
        ->and($repository->forOwner(new UserId(5))->contains(new ProductId(1)))->toBeTrue();
})->with('shopping list repositories');
