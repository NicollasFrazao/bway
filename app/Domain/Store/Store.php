<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\Name;

/**
 * A supermarket laid out as sectors and partitions on a grid (aggregate root).
 *
 * A store is valid from the moment it exists: exactly one of its sectors is the entrance, and no
 * sector or partition appears twice.
 */
final readonly class Store
{
    /**
     * The sectors, keyed by sector id, in the order given.
     *
     * @var array<int, Sector>
     */
    private array $sectors;

    /**
     * The partitions, keyed by partition id, in the order given.
     *
     * @var array<int, Partition>
     */
    private array $partitions;

    /**
     * The sector every visit starts from.
     */
    private Sector $entrance;

    /**
     * Assembles a store and checks its layout.
     *
     * @param  list<Sector>  $sectors  Every sector of the store, the entrance included.
     * @param  list<Partition>  $partitions  Every shelf and divider; may be empty.
     *
     * @throws DuplicateSector when two sectors share an id.
     * @throws DuplicatePartition when two partitions share an id.
     * @throws StoreWithoutEntrance when no sector is the entrance.
     * @throws StoreWithSeveralEntrances when more than one sector is the entrance.
     */
    public function __construct(
        public StoreId $id,
        public Name $name,
        array $sectors,
        array $partitions,
    ) {
        $this->sectors = $this->indexSectors($sectors);
        $this->partitions = $this->indexPartitions($partitions);
        $this->entrance = $this->onlyEntrance($sectors);
    }

    /**
     * The sector every visit starts from.
     */
    public function entrance(): Sector
    {
        return $this->entrance;
    }

    /**
     * Every sector, the entrance included, in the order the store was assembled with.
     *
     * @return list<Sector>
     */
    public function sectors(): array
    {
        return array_values($this->sectors);
    }

    /**
     * Every partition, in the order the store was assembled with.
     *
     * @return list<Partition>
     */
    public function partitions(): array
    {
        return array_values($this->partitions);
    }

    /**
     * Finds one sector of this store.
     *
     * @throws SectorNotInStore when the store has no sector with that id.
     */
    public function sector(SectorId $id): Sector
    {
        return $this->sectors[$id->value] ?? throw SectorNotInStore::forSector($this->id, $id);
    }

    /**
     * Keys the sectors by id, refusing a repeated one.
     *
     * @param  list<Sector>  $sectors
     * @return array<int, Sector>
     *
     * @throws DuplicateSector when two sectors share an id.
     */
    private function indexSectors(array $sectors): array
    {
        $indexed = [];

        foreach ($sectors as $sector) {
            if (isset($indexed[$sector->id->value])) {
                throw DuplicateSector::inStore($this->id, $sector->id);
            }

            $indexed[$sector->id->value] = $sector;
        }

        return $indexed;
    }

    /**
     * Keys the partitions by id, refusing a repeated one.
     *
     * @param  list<Partition>  $partitions
     * @return array<int, Partition>
     *
     * @throws DuplicatePartition when two partitions share an id.
     */
    private function indexPartitions(array $partitions): array
    {
        $indexed = [];

        foreach ($partitions as $partition) {
            if (isset($indexed[$partition->id->value])) {
                throw DuplicatePartition::inStore($this->id, $partition->id);
            }

            $indexed[$partition->id->value] = $partition;
        }

        return $indexed;
    }

    /**
     * Picks the entrance, which must be exactly one sector.
     *
     * @param  list<Sector>  $sectors
     *
     * @throws StoreWithoutEntrance when no sector is the entrance.
     * @throws StoreWithSeveralEntrances when more than one sector is the entrance.
     */
    private function onlyEntrance(array $sectors): Sector
    {
        $entrances = array_values(array_filter(
            $sectors,
            static fn (Sector $sector): bool => $sector->isEntrance(),
        ));

        if ($entrances === []) {
            throw StoreWithoutEntrance::forStore($this->id);
        }

        if (count($entrances) > 1) {
            throw StoreWithSeveralEntrances::forStore(
                $this->id,
                ...array_map(static fn (Sector $sector): SectorId => $sector->id, $entrances),
            );
        }

        return $entrances[0];
    }
}
