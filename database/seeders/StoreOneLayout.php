<?php

declare(strict_types=1);

namespace Database\Seeders;

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
 * Store 1 as v1 left it at the `v1` tag: its sectors with their rectangles and entrance, and its
 * partitions.
 *
 * The rectangles are those of v1's mapping (`tests/Fixtures/v1/store-1/grid.json`, `setores` and
 * `divisorias`), and the sector ids those its routes prove (tests/Support/V1StoreOne.php). v1's
 * database, with the sectors' names and which one was the entrance, is not in the repository:
 * sector 2 is the entrance, as in the golden tests, and the names are this seed's.
 */
final class StoreOneLayout
{
    /**
     * The id of the store.
     */
    public const int STORE_ID = 1;

    /**
     * The entrance: v1's id and [x, y, width, height].
     */
    private const array ENTRANCE = [2, [2, 2, 368, 88]];

    /**
     * The sales sectors: v1's id => [name, [x, y, width, height]]. Each sells the catalogue
     * department of its name (ProductCatalogSeeder::DEPARTMENTS).
     */
    private const array SALES_SECTORS = [
        3 => ['Bakery and breakfast', [139, 92, 124, 79]],
        5 => ['Household and personal care', [2, 173, 96, 201]],
        6 => ['Grocery', [99, 173, 271, 122]],
        10 => ['Drinks', [372, 297, 104, 78]],
        11 => ['Fresh and frozen food', [101, 377, 269, 111]],
    ];

    /**
     * The partitions: id => [x, y, width, height].
     */
    private const array PARTITIONS = [
        1 => [1, 91, 137, 81],
        2 => [264, 91, 107, 81],
        3 => [99, 296, 272, 80],
        4 => [1, 375, 98, 116],
    ];

    /**
     * The store as a domain aggregate, which checks the layout: one entrance, no overlap.
     */
    public static function store(): Store
    {
        [$entranceId, $entranceArea] = self::ENTRANCE;
        $sectors = [new Sector(new SectorId($entranceId), Name::from('Entrance'), self::area($entranceArea), SectorKind::Entrance)];

        foreach (self::SALES_SECTORS as $id => [$name, $area]) {
            $sectors[] = new Sector(new SectorId($id), Name::from($name), self::area($area), SectorKind::Sales);
        }

        $partitions = [];

        foreach (self::PARTITIONS as $id => $area) {
            $partitions[] = new Partition(new PartitionId($id), self::area($area));
        }

        return new Store(new StoreId(self::STORE_ID), Name::from('Demo supermarket'), $sectors, $partitions);
    }

    /**
     * The catalogue department each sales sector sells.
     *
     * @return array<int, string> Department names, keyed by sector id.
     */
    public static function departments(): array
    {
        return array_map(static fn (array $sector): string => $sector[0], self::SALES_SECTORS);
    }

    /**
     * An area from [x, y, width, height].
     *
     * @param  array{int, int, int, int}  $area  The top-left cell, the width and the height.
     */
    private static function area(array $area): GridArea
    {
        return new GridArea(new GridCoordinate($area[0], $area[1]), $area[2], $area[3]);
    }
}
