<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Shared\Name;
use App\Domain\Store\Partition;
use App\Domain\Store\PartitionId;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use App\Infrastructure\Persistence\Eloquent\PartitionRecord;
use App\Infrastructure\Persistence\Eloquent\SectorRecord;
use App\Infrastructure\Persistence\Eloquent\StoreRecord;

/**
 * Stores kept in `stores`, with their layout in `sectors` and `partitions`; each is rebuilt as a
 * Store aggregate, which checks the layout again on the way out.
 */
final readonly class EloquentStoreRepository implements StoreRepository
{
    /**
     * The relations loaded with every store.
     */
    private const array LAYOUT = ['sectors', 'partitions'];

    /**
     * Loads one store with its sectors and partitions, each in the order of their ids.
     *
     * @throws StoreNotFound when no row has that id.
     */
    public function get(StoreId $id): Store
    {
        $record = StoreRecord::query()->with(self::LAYOUT)->find($id->value) ?? throw StoreNotFound::withId($id);

        return $this->store($record);
    }

    /**
     * Every store, in the order of their ids.
     *
     * @return list<Store>
     */
    public function all(): array
    {
        return array_values(
            StoreRecord::query()->with(self::LAYOUT)->orderBy('id')->get()->map($this->store(...))->all(),
        );
    }

    /**
     * The aggregate a row and its layout stand for.
     */
    private function store(StoreRecord $record): Store
    {
        return new Store(
            new StoreId($record->id),
            Name::from($record->name),
            array_values($record->sectors->map($this->sector(...))->all()),
            array_values($record->partitions->map($this->partition(...))->all()),
        );
    }

    /**
     * The sector a row stands for.
     */
    private function sector(SectorRecord $record): Sector
    {
        return new Sector(
            new SectorId($record->id),
            Name::from($record->name),
            new GridArea(new GridCoordinate($record->x, $record->y), $record->width, $record->height),
            $record->kind,
        );
    }

    /**
     * The partition a row stands for.
     */
    private function partition(PartitionRecord $record): Partition
    {
        return new Partition(
            new PartitionId($record->id),
            new GridArea(new GridCoordinate($record->x, $record->y), $record->width, $record->height),
        );
    }
}
