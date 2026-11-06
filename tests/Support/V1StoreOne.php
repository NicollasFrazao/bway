<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Grid\Grid;
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
use App\Infrastructure\FloorPlan\GdFloorPlanDecoder;
use LogicException;

/**
 * Store 1 as v1 left it at the `v1` tag, read from `tests/Fixtures/v1/store-1/`.
 *
 * The files are copied verbatim, so their keys are v1's Portuguese ones: `floor-plan.jpg` was
 * `storage/app/public/estabelecimentos/plantas/estabelecimento.jpg`, `grid.json` was
 * `mapeamentos/1.json` (the grid v1 mapped from that plan, with the sectors and partitions it found)
 * and `routes/<from>-<to>.json` were `rotas/1-<from>-<to>.json`, the 36 routes v1 stored.
 *
 * @phpstan-type V1Area array{x: int, y: int, largura: int, comprimento: int}
 * @phpstan-type V1Mapping array{ds_mapeamento: list<list<int>>, setores: list<V1Area>, divisorias: list<V1Area>}
 */
final class V1StoreOne
{
    /**
     * The sector ids of store 1, in the order v1's mapping listed their areas (`setores`).
     *
     * The sectors themselves lived in v1's database, which is not in the repository. The order is
     * proven by the routes: each starts at the centre of its first sector's area and ends at the
     * centre of the second's (tests/Feature/Golden/StoreOneTest.php checks it).
     */
    public const array SECTOR_IDS = [2, 3, 5, 6, 10, 11];

    /**
     * Where the fixtures are.
     */
    private const string FIXTURES = __DIR__.'/../Fixtures/v1/store-1';

    /**
     * The grid PHP maps from the floor plan, kept for the whole run: mapping it takes a moment.
     */
    private static ?Grid $mapped = null;

    /**
     * The mapping file, decoded once for the whole run.
     *
     * @var V1Mapping|null
     */
    private static ?array $mapping = null;

    /**
     * The bytes of the floor plan v1 mapped.
     */
    public static function floorPlan(): string
    {
        return (string) file_get_contents(self::FIXTURES.'/floor-plan.jpg');
    }

    /**
     * The grid PHP maps from v1's floor plan, decoded with GD.
     */
    public static function mappedByPhp(): Grid
    {
        return self::$mapped ??= new FloorPlanMapper(new LegendColorClassifier, new GdFloorPlanDecoder)
            ->mapFile(self::floorPlan());
    }

    /**
     * The grid v1 mapped, as the codes it wrote (0 floor, 1 wall, 2 sector, 3 partition, 4 walkway).
     *
     * @return list<list<int>>
     */
    public static function grid(): array
    {
        return self::mapping()['ds_mapeamento'];
    }

    /**
     * The code v1's grid holds for a cell.
     *
     * @throws LogicException when the cell is not on the grid.
     */
    public static function codeAt(GridCoordinate $cell): int
    {
        return self::grid()[$cell->y][$cell->x] ?? throw new LogicException("v1's grid has no cell ({$cell->x}, {$cell->y}).");
    }

    /**
     * The area of each sector v1's mapping found, keyed by sector id.
     *
     * @return array<int, GridArea>
     */
    public static function sectorAreas(): array
    {
        return array_combine(self::SECTOR_IDS, array_map(self::area(...), self::mapping()['setores']));
    }

    /**
     * The area of one sector.
     *
     * @throws LogicException when store 1 has no such sector.
     */
    public static function sectorArea(int $sectorId): GridArea
    {
        return self::sectorAreas()[$sectorId] ?? throw new LogicException("Store 1 has no sector {$sectorId}.");
    }

    /**
     * Store 1 with v1's sectors and partitions.
     *
     * Sector 2 is made the entrance: v1's database held the flag, and no route depends on it.
     */
    public static function store(): Store
    {
        $sectors = [];

        foreach (self::sectorAreas() as $id => $area) {
            $kind = $id === 2 ? SectorKind::Entrance : SectorKind::Sales;
            $sectors[] = new Sector(new SectorId($id), Name::from("Sector {$id}"), $area, $kind);
        }

        $partitions = array_map(
            static fn (array $area, int $index): Partition => new Partition(new PartitionId($index + 1), self::area($area)),
            self::mapping()['divisorias'],
            array_keys(self::mapping()['divisorias']),
        );

        return new Store(new StoreId(1), Name::from('Store 1'), $sectors, $partitions);
    }

    /**
     * The route v1 stored for the pair, as cells from the start to the target; empty when v1 stored
     * none (the same sector).
     *
     * The file lists the route target first, with the target twice and without the start, whose
     * cell is the `parent` of the last entry ("186x46"). This undoes all three.
     *
     * @return list<GridCoordinate>
     */
    public static function route(int $from, int $to): array
    {
        /**
         * The entries as v1 wrote them.
         *
         * @var list<array{vl_x: int, vl_y: int, parent: string}> $stored
         */
        $stored = self::json("routes/{$from}-{$to}.json");
        $last = $stored[count($stored) - 1] ?? null;

        if ($last === null) {
            return [];
        }

        $walked = array_map(
            static fn (array $entry): GridCoordinate => new GridCoordinate($entry['vl_x'], $entry['vl_y']),
            array_reverse(array_slice($stored, 1)),
        );

        return [self::parentCell($last['parent']), ...$walked];
    }

    /**
     * The cell of a `parent` reference, written "<x>x<y>".
     */
    private static function parentCell(string $parent): GridCoordinate
    {
        $coordinates = array_map(intval(...), explode('x', $parent));

        return new GridCoordinate($coordinates[0], $coordinates[1] ?? -1);
    }

    /**
     * The decoded mapping file.
     *
     * @return V1Mapping
     */
    private static function mapping(): array
    {
        if (self::$mapping === null) {
            /**
             * The keys this class reads, as v1 wrote them.
             *
             * @var V1Mapping $decoded
             */
            $decoded = self::json('grid.json');
            self::$mapping = $decoded;
        }

        return self::$mapping;
    }

    /**
     * An area as v1's mapping wrote it.
     *
     * @param  V1Area  $area  Top-left cell, width (`largura`) and height (`comprimento`).
     */
    private static function area(array $area): GridArea
    {
        return new GridArea(new GridCoordinate($area['x'], $area['y']), $area['largura'], $area['comprimento']);
    }

    /**
     * A decoded fixture file.
     */
    private static function json(string $file): mixed
    {
        return json_decode((string) file_get_contents(self::FIXTURES.'/'.$file), true, flags: JSON_THROW_ON_ERROR);
    }
}
