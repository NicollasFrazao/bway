<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductId;
use App\Domain\Store\Assortment;
use App\Domain\Store\ProductInSeveralSectors;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\StoreId;

it('tells the sector a product is sold in', function (): void {
    $assortment = new Assortment(
        new StoreId(1),
        new SectorProduct(new SectorId(2), new ProductId(7)),
        new SectorProduct(new SectorId(3), new ProductId(8)),
    );

    expect($assortment->sectorSelling(new ProductId(7)))->toEqual(new SectorId(2))
        ->and($assortment->sectorSelling(new ProductId(8)))->toEqual(new SectorId(3));
});

it('tells a product the store does not sell by null', function (): void {
    $assortment = new Assortment(new StoreId(1), new SectorProduct(new SectorId(2), new ProductId(7)));

    expect($assortment->sectorSelling(new ProductId(9)))->toBeNull()
        ->and(new Assortment(new StoreId(1))->sectorSelling(new ProductId(7)))->toBeNull();
});

it('refuses a product placed in two sectors, or twice in one', function (int $secondSector): void {
    expect(fn (): Assortment => new Assortment(
        new StoreId(1),
        new SectorProduct(new SectorId(2), new ProductId(7)),
        new SectorProduct(new SectorId($secondSector), new ProductId(7)),
    ))->toThrow(
        ProductInSeveralSectors::class,
        "Store 1 places product 7 in sector 2 and again in sector {$secondSector}; a product is sold in one sector of a store.",
    );
})->with(['another sector' => 3, 'the same sector' => 2]);
