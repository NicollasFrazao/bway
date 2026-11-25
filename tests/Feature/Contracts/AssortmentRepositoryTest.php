<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductId;
use App\Domain\Shared\Name;
use App\Domain\Store\Assortment;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\EloquentAssortmentRepository;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Domain\InMemoryAssortmentRepository;
use Tests\Support\StoreLayout;

uses(RefreshDatabase::class);

/**
 * What store 1 of the contract sells: product 7 in sector 2, product 8 in sector 3.
 *
 * @return list<SectorProduct>
 */
function storeOneSells(): array
{
    return [
        new SectorProduct(new SectorId(2), new ProductId(7)),
        new SectorProduct(new SectorId(3), new ProductId(8)),
    ];
}

/*
 * The contract every AssortmentRepository keeps, given that store 1 sells product 7 in sector 2 and
 * product 8 in sector 3, and nothing is recorded for store 2. The in-memory adapter is the
 * reference; the Eloquent adapter runs it on MySQL.
 */
dataset('assortment repositories', [
    'in memory' => fn (): AssortmentRepository => new InMemoryAssortmentRepository(new Assortment(new StoreId(1), ...storeOneSells())),
    'eloquent' => function (): AssortmentRepository {
        $writer = new StoreWriter;
        $writer->store(new Store(new StoreId(1), Name::from('Downtown'), [StoreLayout::entrance(1), StoreLayout::sector(2), StoreLayout::sector(3)], []));
        $writer->store(new Store(new StoreId(2), Name::from('Harbour'), [StoreLayout::entrance(4)], []));
        ProductRecord::query()->forceCreate(['id' => 7, 'name' => 'Rice']);
        ProductRecord::query()->forceCreate(['id' => 8, 'name' => 'Milk']);
        $writer->assortment(new StoreId(1), ...storeOneSells());

        return new EloquentAssortmentRepository;
    },
]);

it('loads the assortment of a store', function (AssortmentRepository $repository): void {
    $assortment = $repository->forStore(new StoreId(1));

    expect($assortment->storeId)->toEqual(new StoreId(1))
        ->and($assortment->sectorSelling(new ProductId(7)))->toEqual(new SectorId(2))
        ->and($assortment->sectorSelling(new ProductId(8)))->toEqual(new SectorId(3))
        ->and($assortment->sectorSelling(new ProductId(9)))->toBeNull();
})->with('assortment repositories');

it('gives an empty assortment to a store with nothing recorded', function (AssortmentRepository $repository): void {
    $assortment = $repository->forStore(new StoreId(2));

    expect($assortment->storeId)->toEqual(new StoreId(2))
        ->and($assortment->sectorSelling(new ProductId(7)))->toBeNull();
})->with('assortment repositories');
