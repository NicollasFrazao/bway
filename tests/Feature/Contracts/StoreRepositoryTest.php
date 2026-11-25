<?php

declare(strict_types=1);

use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Shared\Name;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorKind;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use App\Infrastructure\Persistence\EloquentStoreRepository;
use Database\Seeders\StoreWriter;
use Illuminate\Database\ConnectionInterface;
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

        return new EloquentStoreRepository(app(ConnectionInterface::class));
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
    'eloquent' => fn (): StoreRepository => new EloquentStoreRepository(app(ConnectionInterface::class)),
]);

it('adds a store with its entrance, and gives it fresh ids', function (StoreRepository $repository): void {
    $store = $repository->add(Name::from('Riverside'), new GridArea(new GridCoordinate(0, 0), 2, 2));

    // The database numbers new rows past any it ever gave, so only "fresh" is part of the contract.
    expect($repository->get($store->id))->toEqual($store)
        ->and($store->id->value)->toBeGreaterThan(2)
        ->and($store->entrance()->name->value)->toBe('Entrance')
        ->and($store->entrance()->id->value)->toBeGreaterThan(5)
        ->and($repository->nextSectorId()->value)->toBe($store->entrance()->id->value + 1)
        ->and($repository->nextPartitionId()->value)->toBe(4);
})->with('store repositories');

it('saves a changed layout: new parts added, changed ones rewritten, missing ones removed', function (StoreRepository $repository): void {
    $changed = new Store(
        new StoreId(2),
        Name::from('Harbour market'),
        [
            new Sector(new SectorId(3), Name::from('Sector 3'), new GridArea(new GridCoordinate(9, 0), 2, 2), SectorKind::Sales),
            new Sector(new SectorId(4), Name::from('Front door'), new GridArea(new GridCoordinate(12, 0), 2, 2), SectorKind::Entrance),
            StoreLayout::sector(7),
        ],
        [StoreLayout::partition(2)],
    );

    $repository->save($changed);

    expect($repository->get(new StoreId(2)))->toEqual($changed)
        ->and($repository->get(new StoreId(1)))->toEqual(downtownStore());
})->with('store repositories');

it('does not save a store that does not exist', function (StoreRepository $repository): void {
    $repository->save(new Store(new StoreId(9), Name::from('Nowhere'), [StoreLayout::entrance(8)], []));
})->with('store repositories')->throws(StoreNotFound::class);
