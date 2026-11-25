<?php

declare(strict_types=1);

use App\Domain\Shared\Name;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use App\Infrastructure\Persistence\EloquentStoreRepository;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\StoreLayout;

uses(RefreshDatabase::class);

/**
 * Store 1 of the contract: an entrance, a sales sector and a partition.
 */
function downtownStore(): Store
{
    return new Store(
        new StoreId(1),
        Name::from('Downtown'),
        [StoreLayout::entrance(1), StoreLayout::sector(2)],
        [StoreLayout::partition(1)],
    );
}

/**
 * Store 2 of the contract: an entrance, two sales sectors and two partitions.
 */
function harbourStore(): Store
{
    return new Store(
        new StoreId(2),
        Name::from('Harbour'),
        [StoreLayout::entrance(3), StoreLayout::sector(4), StoreLayout::sector(5)],
        [StoreLayout::partition(2), StoreLayout::partition(3)],
    );
}

/*
 * The contract every StoreRepository keeps, given stores 1 and 2. The in-memory adapter is the
 * reference; the Eloquent adapter runs it on MySQL.
 */
dataset('store repositories', [
    'in memory' => fn (): StoreRepository => new InMemoryStoreRepository(downtownStore(), harbourStore()),
    'eloquent' => function (): StoreRepository {
        $writer = new StoreWriter;
        $writer->store(downtownStore());
        $writer->store(harbourStore());

        return new EloquentStoreRepository;
    },
]);

it('loads a store by id with its whole layout', function (StoreRepository $repository): void {
    $store = $repository->get(new StoreId(2));

    expect($store)->toEqual(harbourStore())
        ->and($store->entrance()->id->value)->toBe(3);
})->with('store repositories');

it('reports a store that does not exist', function (StoreRepository $repository): void {
    expect(fn (): Store => $repository->get(new StoreId(9)))
        ->toThrow(StoreNotFound::class, 'No store has id 9.');
})->with('store repositories');

it('lists every store', function (StoreRepository $repository): void {
    expect($repository->all())->toEqual([downtownStore(), harbourStore()]);
})->with('store repositories');

it('lists no store when there is none', function (StoreRepository $repository): void {
    expect($repository->all())->toBe([]);
})->with([
    'in memory' => fn (): StoreRepository => new InMemoryStoreRepository,
    'eloquent' => fn (): StoreRepository => new EloquentStoreRepository,
]);
