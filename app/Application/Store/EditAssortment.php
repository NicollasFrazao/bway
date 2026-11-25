<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\Store\Assortment;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * Places the products of the catalogue in the sectors of a store (use case); v1 had to be edited in
 * the database.
 */
final readonly class EditAssortment
{
    /**
     * Takes the assortments, the stores and the catalogue.
     */
    public function __construct(
        private AssortmentRepository $assortments,
        private StoreRepository $stores,
        private ProductCatalog $catalog,
    ) {}

    /**
     * The store, every product and the current assortment.
     *
     * @throws StoreNotFound when no store has the id.
     */
    public function show(StoreId $storeId): AssortmentView
    {
        return new AssortmentView(
            $this->stores->get($storeId),
            $this->catalog->search(''),
            $this->assortments->forStore($storeId),
        );
    }

    /**
     * Replaces the assortment: each product in the sector given, or not sold when it has none.
     *
     * @param  array<int, int>  $placements  Sector ids keyed by product id.
     *
     * @throws StoreNotFound when no store has the id.
     * @throws SectorNotInStore when a sector is not the store's.
     * @throws ProductNotFound when a product is not in the catalogue.
     */
    public function save(StoreId $storeId, array $placements): void
    {
        $store = $this->stores->get($storeId);
        $sectorProducts = [];

        foreach ($placements as $productId => $sectorId) {
            $sector = $store->sector(new SectorId($sectorId));
            $product = $this->catalog->get(new ProductId($productId));
            $sectorProducts[] = new SectorProduct($sector->id, $product->id);
        }

        $this->assortments->save(Assortment::ofPlacements($storeId, ...$sectorProducts));
    }
}
