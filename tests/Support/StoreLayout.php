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

/**
 * Builds the parts of a store for tests that care only about ids and kinds, not about the geometry.
 *
 * Each part gets its own 2 x 2 area, so no two parts overlap: sector or entrance n at (3n, 0),
 * partition n at (3n, 3).
 */
final class StoreLayout
{
    /**
     * A sales sector with the given id, on a 2 x 2 area.
     */
    public static function sector(int $id): Sector
    {
        return new Sector(new SectorId($id), Name::from("Sector {$id}"), self::area(3 * $id, 0), SectorKind::Sales);
    }

    /**
     * An entrance sector with the given id, on a 2 x 2 area.
     */
    public static function entrance(int $id): Sector
    {
        return new Sector(new SectorId($id), Name::from('Entrance'), self::area(3 * $id, 0), SectorKind::Entrance);
    }

    /**
     * A partition with the given id, on a 2 x 2 area.
     */
    public static function partition(int $id): Partition
    {
        return new Partition(new PartitionId($id), self::area(3 * $id, 3));
    }

    /**
     * A 2 x 2 area with its top-left cell at (x, y).
     */
    private static function area(int $x, int $y): GridArea
    {
        return new GridArea(new GridCoordinate($x, $y), 2, 2);
    }
}
