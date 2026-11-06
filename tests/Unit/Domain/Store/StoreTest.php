<?php

declare(strict_types=1);

use App\Domain\Shared\Name;
use App\Domain\Store\DuplicatePartition;
use App\Domain\Store\DuplicateSector;
use App\Domain\Store\Partition;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreWithoutEntrance;
use App\Domain\Store\StoreWithSeveralEntrances;
use Tests\Support\StoreLayout;

/**
 * Store 1, with the given sectors and partitions.
 *
 * @param  list<Sector>  $sectors  The entrance included, when the test wants one.
 * @param  list<Partition>  $partitions  None by default.
 */
function store(array $sectors, array $partitions = []): Store
{
    return new Store(new StoreId(1), Name::from('Downtown'), $sectors, $partitions);
}

it('assembles a store and finds its entrance', function (): void {
    $entrance = StoreLayout::entrance(1);
    $bakery = StoreLayout::sector(2);
    $shelf = StoreLayout::partition(1);

    $store = store([$bakery, $entrance], [$shelf]);

    expect($store->id)->toEqual(new StoreId(1))
        ->and($store->name->value)->toBe('Downtown')
        ->and($store->entrance())->toBe($entrance)
        ->and($store->sectors())->toBe([$bakery, $entrance])
        ->and($store->partitions())->toBe([$shelf]);
});

it('accepts a store with no partitions', function (): void {
    expect(store([StoreLayout::entrance(1)])->partitions())->toBe([]);
});

it('refuses a store without an entrance', function (): void {
    expect(fn (): Store => store([StoreLayout::sector(1), StoreLayout::sector(2)]))
        ->toThrow(StoreWithoutEntrance::class, 'Store 1 has no entrance sector; exactly one is required.');
});

it('refuses a store with no sectors at all, which has no entrance either', function (): void {
    expect(fn (): Store => store([]))->toThrow(StoreWithoutEntrance::class);
});

it('refuses a store with more than one entrance, naming them', function (): void {
    $sectors = [StoreLayout::entrance(1), StoreLayout::sector(2), StoreLayout::entrance(3)];

    expect(fn (): Store => store($sectors))
        ->toThrow(StoreWithSeveralEntrances::class, 'Store 1 has 2 entrance sectors (1, 3); exactly one is required.');
});

it('refuses two sectors with the same id', function (): void {
    $sectors = [StoreLayout::entrance(1), StoreLayout::sector(2), StoreLayout::sector(2)];

    expect(fn (): Store => store($sectors))
        ->toThrow(DuplicateSector::class, 'Store 1 lists sector 2 more than once.');
});

it('refuses two partitions with the same id', function (): void {
    $partitions = [StoreLayout::partition(4), StoreLayout::partition(4)];

    expect(fn (): Store => store([StoreLayout::entrance(1)], $partitions))
        ->toThrow(DuplicatePartition::class, 'Store 1 lists partition 4 more than once.');
});

it('finds one of its sectors by id', function (): void {
    $bakery = StoreLayout::sector(2);

    expect(store([StoreLayout::entrance(1), $bakery])->sector(new SectorId(2)))->toBe($bakery);
});

it('refuses a sector it does not have', function (): void {
    expect(fn (): Sector => store([StoreLayout::entrance(1)])->sector(new SectorId(9)))
        ->toThrow(SectorNotInStore::class, 'Store 1 has no sector 9.');
});
