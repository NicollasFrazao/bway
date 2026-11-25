<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Catalog\ProductId;
use App\Domain\Store\Assortment;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\SectorProductRecord;
use Illuminate\Database\ConnectionInterface;

/**
 * The assortment of each store, kept in `sector_products`.
 */
final readonly class EloquentAssortmentRepository implements AssortmentRepository
{
    /**
     * Takes the connection whose transaction makes a save all or nothing.
     */
    public function __construct(private ConnectionInterface $connection) {}

    /**
     * Loads what the store sells and where; no row for the store is an empty assortment.
     */
    public function forStore(StoreId $storeId): Assortment
    {
        $sectorProducts = SectorProductRecord::query()
            ->where('store_id', $storeId->value)
            ->orderBy('id')
            ->get()
            ->map(static fn (SectorProductRecord $record): SectorProduct => new SectorProduct(
                new SectorId($record->sector_id),
                new ProductId($record->product_id),
            ))
            ->all();

        return new Assortment($storeId, ...$sectorProducts);
    }

    /**
     * Replaces the store's rows with the assortment's, in one transaction.
     */
    public function save(Assortment $assortment): void
    {
        $this->connection->transaction(static function () use ($assortment): void {
            SectorProductRecord::query()->where('store_id', $assortment->storeId->value)->delete();
            SectorProductRecord::query()->insert(array_map(
                static fn (SectorProduct $sectorProduct): array => [
                    'store_id' => $assortment->storeId->value,
                    'sector_id' => $sectorProduct->sectorId->value,
                    'product_id' => $sectorProduct->productId->value,
                ],
                $assortment->sectorProducts(),
            ));
        });
    }
}
