<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Grid\InvalidGridArea;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorKind;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * Adds, changes and removes the sectors of a store (use case). Each change builds the new Store
 * aggregate first, so a layout the domain refuses is never written.
 */
final readonly class EditSectors
{
    /**
     * Takes the stores and the writer of layouts.
     */
    public function __construct(
        private StoreRepository $stores,
        private LayoutWriter $writer,
    ) {}

    /**
     * Adds a sales sector.
     *
     * @throws StoreNotFound when no store has the id.
     * @throws InvalidName when the name is blank or too long.
     * @throws InvalidGridArea when the area starts at a negative cell or has no width or height.
     * @throws LayoutRefused when it overlaps another part or reaches beyond the grid.
     */
    public function add(StoreId $storeId, string $name, AreaInput $area): SectorId
    {
        $store = $this->stores->get($storeId);
        $sector = new Sector($this->stores->nextSectorId(), Name::from($name), $area->area(), SectorKind::Sales);
        $this->writer->write($store, [...$store->sectors(), $sector], $store->partitions());

        return $sector->id;
    }

    /**
     * Renames or moves a sector; the entrance stays the entrance.
     *
     * @throws StoreNotFound when no store has the id.
     * @throws SectorNotInStore when the sector is not the store's.
     * @throws InvalidName when the name is blank or too long.
     * @throws InvalidGridArea when the area starts at a negative cell or has no width or height.
     * @throws LayoutRefused when it overlaps another part or reaches beyond the grid.
     */
    public function update(StoreId $storeId, SectorId $sectorId, string $name, AreaInput $area): void
    {
        $store = $this->stores->get($storeId);
        $old = $store->sector($sectorId);
        $new = new Sector($old->id, Name::from($name), $area->area(), $old->kind);
        $sectors = array_map(
            static fn (Sector $sector): Sector => $sector->id->value === $sectorId->value ? $new : $sector,
            $store->sectors(),
        );
        $this->writer->write($store, $sectors, $store->partitions());
    }

    /**
     * Removes a sales sector, with what the store sells there and its routes.
     *
     * @throws StoreNotFound when no store has the id.
     * @throws SectorNotInStore when the sector is not the store's.
     * @throws LayoutRefused when the sector is the entrance.
     */
    public function remove(StoreId $storeId, SectorId $sectorId): void
    {
        $store = $this->stores->get($storeId);
        $store->sector($sectorId);
        $sectors = array_values(array_filter(
            $store->sectors(),
            static fn (Sector $sector): bool => $sector->id->value !== $sectorId->value,
        ));
        $this->writer->write($store, $sectors, $store->partitions());
    }
}
