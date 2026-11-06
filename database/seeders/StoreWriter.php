<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Store\Assortment;
use App\Domain\Store\ProductInSeveralSectors;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\PartitionRecord;
use App\Infrastructure\Persistence\Eloquent\SectorProductRecord;
use App\Infrastructure\Persistence\Eloquent\SectorRecord;
use App\Infrastructure\Persistence\Eloquent\StoreRecord;

/**
 * Writes a store built as a domain aggregate, and its assortment, to the database, keeping every id.
 *
 * The StoreRepository port only reads (creating stores is B7's, issue #10), so the seeders and the
 * repository tests write through this class. Building the aggregate first means a layout the domain
 * refuses never reaches the tables.
 */
final class StoreWriter
{
    /**
     * Inserts the store, its sectors and its partitions with their ids.
     */
    public function store(Store $store): void
    {
        StoreRecord::query()->forceCreate(['id' => $store->id->value, 'name' => $store->name->value]);

        foreach ($store->sectors() as $sector) {
            SectorRecord::query()->forceCreate([
                'id' => $sector->id->value,
                'store_id' => $store->id->value,
                'name' => $sector->name->value,
                'kind' => $sector->kind,
                'x' => $sector->area->origin->x,
                'y' => $sector->area->origin->y,
                'width' => $sector->area->width,
                'height' => $sector->area->height,
            ]);
        }

        foreach ($store->partitions() as $partition) {
            PartitionRecord::query()->forceCreate([
                'id' => $partition->id->value,
                'store_id' => $store->id->value,
                'x' => $partition->area->origin->x,
                'y' => $partition->area->origin->y,
                'width' => $partition->area->width,
                'height' => $partition->area->height,
            ]);
        }
    }

    /**
     * Inserts what the store sells and where, after checking it as an Assortment.
     *
     * @throws ProductInSeveralSectors when a product is placed more than once.
     */
    public function assortment(StoreId $storeId, SectorProduct ...$sectorProducts): void
    {
        new Assortment($storeId, ...$sectorProducts);

        foreach ($sectorProducts as $sectorProduct) {
            SectorProductRecord::query()->create([
                'store_id' => $storeId->value,
                'sector_id' => $sectorProduct->sectorId->value,
                'product_id' => $sectorProduct->productId->value,
            ]);
        }
    }
}
