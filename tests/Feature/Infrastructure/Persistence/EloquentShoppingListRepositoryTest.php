<?php

declare(strict_types=1);

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingList;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Persistence\EloquentShoppingListRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    User::factory()->create(['id' => 4]);
    ProductRecord::query()->forceCreate(['id' => 3, 'name' => 'Rice']);
});

it('keeps the name a product had when it was added, as the domain does', function (): void {
    $lists = new EloquentShoppingListRepository(app(ConnectionInterface::class));
    $list = ShoppingList::empty(new UserId(4));
    $list->add(new Product(new ProductId(3), ProductName::from('Rice')));
    $lists->save($list);

    ProductRecord::query()->whereKey(3)->update(['name' => 'White rice']);

    expect($lists->forOwner(new UserId(4))->items()[0]->productName->value ?? null)->toBe('Rice');
});

it('saves a list all or nothing', function (): void {
    $lists = new EloquentShoppingListRepository(app(ConnectionInterface::class));
    $saved = ShoppingList::empty(new UserId(4));
    $saved->add(new Product(new ProductId(3), ProductName::from('Rice')));
    $lists->save($saved);
    $changed = ShoppingList::restore(new UserId(4), ...$saved->items());
    $changed->remove(new ProductId(3));
    $changed->add(new Product(new ProductId(9), ProductName::from('A product the catalogue does not have')));

    expect(fn () => $lists->save($changed))->toThrow(QueryException::class)
        ->and($lists->forOwner(new UserId(4))->items())->toEqual($saved->items());
});
