<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductId;
use App\Domain\Store\Assortment;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\StoreId;
use Tests\Support\Domain\InMemoryAssortmentRepository;

/*
 * The contract every AssortmentRepository keeps, given that store 1 sells product 7 in sector 2 and
 * nothing is recorded for store 2. The in-memory adapter is the reference; the Eloquent adapter
 * joins the dataset when it exists.
 * TODO(#7): run this contract against the Eloquent repository on MySQL.
 */
dataset('assortment repositories', [
    'in memory' => fn (): AssortmentRepository => new InMemoryAssortmentRepository(
        new Assortment(new StoreId(1), new SectorProduct(new SectorId(2), new ProductId(7))),
    ),
]);

it('loads the assortment of a store', function (AssortmentRepository $repository): void {
    $assortment = $repository->forStore(new StoreId(1));

    expect($assortment->storeId)->toEqual(new StoreId(1))
        ->and($assortment->sectorSelling(new ProductId(7)))->toEqual(new SectorId(2));
})->with('assortment repositories');

it('gives an empty assortment to a store with nothing recorded', function (AssortmentRepository $repository): void {
    $assortment = $repository->forStore(new StoreId(2));

    expect($assortment->storeId)->toEqual(new StoreId(2))
        ->and($assortment->sectorSelling(new ProductId(7)))->toBeNull();
})->with('assortment repositories');
