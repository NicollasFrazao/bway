<?php

declare(strict_types=1);

namespace Tests\Support;

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

/**
 * Store 1, small enough to draw: an entrance (sector 1) and a bakery (sector 2) one cell each, on a
 * walkway, and a dairy (sector 3) cut off from it by a partition.
 *
 *     RRR      the floor plan, 3 x 7 pixels (portrait, so the mapper does not turn it)
 *     ROR      entrance (1, 1)
 *     ROR
 *     ROR      bakery (1, 3)
 *     RGR      partition (1, 4)
 *     ROR      dairy (1, 5)
 *     RRR
 */
final class CornerShop
{
    /**
     * The store.
     */
    public static function store(): Store
    {
        return new Store(new StoreId(1), Name::from('Corner shop'), [
            self::sector(1, 'Entrance', 1, SectorKind::Entrance),
            self::sector(2, 'Bakery', 3, SectorKind::Sales),
            self::sector(3, 'Dairy', 5, SectorKind::Sales),
        ], [
            new Partition(new PartitionId(1), new GridArea(new GridCoordinate(1, 4), 1, 1)),
        ]);
    }

    /**
     * Its floor plan, as a text file TextFloorPlanDecoder reads.
     */
    public static function floorPlan(): string
    {
        return implode("\n", ['RRR', 'ROR', 'ROR', 'ROR', 'RGR', 'ROR', 'RRR']);
    }

    /**
     * A one-cell sector on the middle column.
     */
    private static function sector(int $id, string $name, int $y, SectorKind $kind): Sector
    {
        return new Sector(new SectorId($id), Name::from($name), new GridArea(new GridCoordinate(1, $y), 1, 1), $kind);
    }
}
