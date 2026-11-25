<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Catalog\ProductId;
use LogicException;

/**
 * Which products each sector of a store sells: a product is sold in at most one sector of a store.
 *
 * Kept apart from the Store aggregate because it changes with the stock, not with the layout. v1
 * allowed a product in several sectors (`setor_produto`) and then used only the first of them by
 * sector name; v2 makes the one sector explicit, so a visit never depends on a sector's name.
 */
final readonly class Assortment
{
    /**
     * The sector each product is sold in, keyed by product id.
     *
     * @var array<int, SectorId>
     */
    private array $sectors;

    /**
     * Lists what the store sells and where.
     *
     * @throws ProductInSeveralSectors when a product is placed more than once.
     */
    public function __construct(
        public StoreId $storeId,
        SectorProduct ...$sectorProducts,
    ) {
        $sectors = [];

        foreach ($sectorProducts as $sectorProduct) {
            $placed = $sectors[$sectorProduct->productId->value] ?? null;

            if ($placed instanceof SectorId) {
                throw ProductInSeveralSectors::inStore($storeId, $sectorProduct->productId, $placed, $sectorProduct->sectorId);
            }

            $sectors[$sectorProduct->productId->value] = $sectorProduct->sectorId;
        }

        $this->sectors = $sectors;
    }

    /**
     * The assortment of the placements, where a later placement of a product replaces an earlier one,
     * so no product is ever in several sectors.
     */
    public static function ofPlacements(StoreId $storeId, SectorProduct ...$placements): self
    {
        $byProduct = [];

        foreach ($placements as $placement) {
            $byProduct[$placement->productId->value] = $placement;
        }

        try {
            return new self($storeId, ...array_values($byProduct));
        } catch (ProductInSeveralSectors $impossible) {
            throw new LogicException($impossible->getMessage(), 0, $impossible);
        }
    }

    /**
     * The sector the product is sold in, or null when the store does not sell it.
     */
    public function sectorSelling(ProductId $productId): ?SectorId
    {
        return $this->sectors[$productId->value] ?? null;
    }

    /**
     * Every product the store sells with its sector, by product id.
     *
     * @return list<SectorProduct>
     */
    public function sectorProducts(): array
    {
        $sectorProducts = [];

        foreach ($this->sectors as $productId => $sectorId) {
            $sectorProducts[] = new SectorProduct($sectorId, new ProductId($productId));
        }

        return $sectorProducts;
    }
}
