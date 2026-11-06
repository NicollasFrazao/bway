<?php

declare(strict_types=1);

use App\Domain\Shared\Name;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\StoreLayout;

/**
 * A store with only an entrance.
 */
function storeNamed(int $id, string $name): Store
{
    return new Store(new StoreId($id), Name::from($name), [StoreLayout::entrance(1)], []);
}

/*
 * The contract every StoreRepository keeps, given stores 1 and 2. The in-memory adapter is the
 * reference; the Eloquent adapter joins the dataset when it exists.
 * TODO(#7): run this contract against the Eloquent repository on MySQL.
 */
dataset('store repositories', [
    'in memory' => fn (): StoreRepository => new InMemoryStoreRepository(storeNamed(1, 'Downtown'), storeNamed(2, 'Harbour')),
]);

it('loads a store by id', function (StoreRepository $repository): void {
    expect($repository->get(new StoreId(2))->name->value)->toBe('Harbour');
})->with('store repositories');

it('reports a store that does not exist', function (StoreRepository $repository): void {
    expect(fn (): Store => $repository->get(new StoreId(9)))
        ->toThrow(StoreNotFound::class, 'No store has id 9.');
})->with('store repositories');

it('lists every store', function (StoreRepository $repository): void {
    $names = array_map(fn (Store $store): string => $store->name->value, $repository->all());

    expect($names)->toEqualCanonicalizing(['Downtown', 'Harbour']);
})->with('store repositories');
