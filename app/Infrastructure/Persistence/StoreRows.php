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
use App\Domain\Store\SectorKind;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\PartitionRecord;
use App\Infrastructure\Persistence\Eloquent\SectorRecord;
use App\Infrastructure\Persistence\Eloquent\StoreRecord;

/**
 * The rows of a store (`stores`, `sectors`, `partitions`) and the Store aggregate they stand for, for
 * {@see EloquentStoreRepository}; the caller runs the writes in a transaction.
 */
final class StoreRows
{
    /**
     * Writes a store row with its entrance and gives the store's id.
     */
    public static function insert(Name $name, GridArea $entrance): int
    {
        $record = StoreRecord::query()->create(['name' => $name->value]);
        SectorRecord::query()->create([
            'store_id' => $record->id,
            'name' => 'Entrance',
            'kind' => SectorKind::Entrance,
            ...self::area($entrance),
        ]);

        return $record->id;
    }

    /**
     * Rewrites the store's name, sectors and partitions; parts the store no longer has are deleted.
     * Sales sectors are written before the entrance, so moving the entrance never leaves two for a
     * moment (the table allows one).
     */
    public static function rewrite(StoreRecord $record, Store $store): void
    {
        $record->update(['name' => $store->name->value]);
        $sectors = $store->sectors();
        usort($sectors, static fn (Sector $a, Sector $b): int => $a->isEntrance() <=> $b->isEntrance());
        SectorRecord::query()
            ->where('store_id', $record->id)
            ->whereNotIn('id', array_map(static fn (Sector $sector): int => $sector->id->value, $sectors))
            ->delete();

        foreach ($sectors as $sector) {
            SectorRecord::query()->firstOrNew(['id' => $sector->id->value])->forceFill([
                'id' => $sector->id->value,
                'store_id' => $record->id,
                'name' => $sector->name->value,
                'kind' => $sector->kind,
                ...self::area($sector->area),
            ])->save();
        }

        $partitions = $store->partitions();
        PartitionRecord::query()
            ->where('store_id', $record->id)
            ->whereNotIn('id', array_map(static fn (Partition $partition): int => $partition->id->value, $partitions))
            ->delete();

        foreach ($partitions as $partition) {
            PartitionRecord::query()->firstOrNew(['id' => $partition->id->value])
                ->forceFill(['id' => $partition->id->value, 'store_id' => $record->id, ...self::area($partition->area)])
                ->save();
        }
    }

    /**
     * The aggregate a row and its layout stand for.
     */
    public static function toStore(StoreRecord $record): Store
    {
        return new Store(
            new StoreId($record->id),
            Name::from($record->name),
            array_values($record->sectors->map(static fn (SectorRecord $sector): Sector => new Sector(
                new SectorId($sector->id),
                Name::from($sector->name),
                self::toArea($sector->x, $sector->y, $sector->width, $sector->height),
                $sector->kind,
            ))->all()),
            array_values($record->partitions->map(static fn (PartitionRecord $partition): Partition => new Partition(
                new PartitionId($partition->id),
                self::toArea($partition->x, $partition->y, $partition->width, $partition->height),
            ))->all()),
        );
    }

    /**
     * The columns of a rectangle.
     *
     * @return array{x: int, y: int, width: int, height: int}
     */
    private static function area(GridArea $area): array
    {
        return ['x' => $area->origin->x, 'y' => $area->origin->y, 'width' => $area->width, 'height' => $area->height];
    }

    /**
     * The rectangle of the columns.
     */
    private static function toArea(int $x, int $y, int $width, int $height): GridArea
    {
        return new GridArea(new GridCoordinate($x, $y), $width, $height);
    }
}
