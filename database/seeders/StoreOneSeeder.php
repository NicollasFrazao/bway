<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\FloorPlan\MapFloorPlan;
use App\Domain\Catalog\ProductId;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use Illuminate\Database\Seeder;
use LogicException;

/**
 * Seeds store 1 as v1 left it, which v1 never seeded: its layout (StoreOneLayout), what each sector
 * sells, and its floor plan, v1's, mapped to the store's grid by the MapFloorPlan use case.
 */
final class StoreOneSeeder extends Seeder
{
    /**
     * The floor plan of the store, copied from `tests/Fixtures/v1/store-1/floor-plan.jpg`.
     */
    private const string FLOOR_PLAN = __DIR__.'/data/store-1/floor-plan.jpg';

    /**
     * Writes the store and its assortment, then maps its floor plan.
     *
     * @throws LogicException when a product of a sector's department is not in the catalogue.
     */
    public function run(StoreWriter $writer, MapFloorPlan $mapFloorPlan): void
    {
        $storeId = new StoreId(StoreOneLayout::STORE_ID);
        $writer->store(StoreOneLayout::store());
        $writer->assortment($storeId, ...$this->sectorProducts());
        $mapFloorPlan->handle($storeId, (string) file_get_contents(self::FLOOR_PLAN));
    }

    /**
     * Every product of every sales sector's department, in that sector.
     *
     * @return list<SectorProduct>
     *
     * @throws LogicException when a product of a department is not in the catalogue.
     */
    private function sectorProducts(): array
    {
        $sectorProducts = [];

        foreach (StoreOneLayout::departments() as $sectorId => $department) {
            foreach ($this->productIds($department) as $productId) {
                $sectorProducts[] = new SectorProduct(new SectorId($sectorId), new ProductId($productId));
            }
        }

        return $sectorProducts;
    }

    /**
     * The ids of the products of a department of the catalogue.
     *
     * @return list<int>
     *
     * @throws LogicException when the catalogue has no such department, or not all of its products.
     */
    private function productIds(string $department): array
    {
        $names = ProductCatalogSeeder::DEPARTMENTS[$department] ?? [];
        $ids = array_values(ProductRecord::query()
            ->whereIn('name', $names)
            ->get()
            ->map(static fn (ProductRecord $record): int => $record->id)
            ->all());

        if (
            $names === []
            || count($ids) !== count($names)
        ) {
            throw new LogicException("Store 1 sells products of {$department} the catalogue does not have; run ProductCatalogSeeder first.");
        }

        return $ids;
    }
}
