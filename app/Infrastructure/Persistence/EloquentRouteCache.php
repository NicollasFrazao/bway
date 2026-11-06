<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteCache;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\RouteRecord;
use UnexpectedValueException;

/**
 * The routes found between sectors, kept in `routes`: one row per store and ordered pair of sectors,
 * the cells as a JSON list of [x, y] pairs from the start to the target.
 *
 * Both sectors must belong to the store: the table refers to them together with the store.
 */
final readonly class EloquentRouteCache implements RouteCache
{
    /**
     * The values of one cell in a route row: x and y.
     */
    private const int PAIR = 2;

    /**
     * The columns that identify a route.
     */
    private const array KEY = ['store_id', 'from_sector_id', 'to_sector_id'];

    /**
     * Reads the route kept for the pair, if any.
     *
     * @throws UnexpectedValueException when the row does not hold a route this class wrote.
     */
    public function find(StoreId $storeId, SectorId $from, SectorId $to): ?Route
    {
        $record = RouteRecord::query()->where($this->key($storeId, $from, $to))->first();

        if ($record === null) {
            return null;
        }

        return new Route(array_map(
            static fn (array $cell): GridCoordinate => new GridCoordinate($cell[0], $cell[1]),
            $this->cells($record->cells),
        ));
    }

    /**
     * Writes the route for the pair, replacing any kept before; two requests remembering the same
     * pair at once leave one row.
     */
    public function remember(StoreId $storeId, SectorId $from, SectorId $to, Route $route): void
    {
        $cells = array_map(static fn (GridCoordinate $cell): array => [$cell->x, $cell->y], $route->cells);

        RouteRecord::query()->upsert(
            [[...$this->key($storeId, $from, $to), 'cells' => json_encode($cells, JSON_THROW_ON_ERROR)]],
            self::KEY,
            ['cells'],
        );
    }

    /**
     * Deletes the store's routes.
     */
    public function forgetStore(StoreId $storeId): void
    {
        RouteRecord::query()->where('store_id', $storeId->value)->delete();
    }

    /**
     * The values of the columns that identify the route of the pair.
     *
     * @return array{store_id: int, from_sector_id: int, to_sector_id: int}
     */
    private function key(StoreId $storeId, SectorId $from, SectorId $to): array
    {
        return ['store_id' => $storeId->value, 'from_sector_id' => $from->value, 'to_sector_id' => $to->value];
    }

    /**
     * The [x, y] pairs of a route row.
     *
     * @return list<array{int, int}>
     *
     * @throws UnexpectedValueException when the JSON is not a list of [x, y] integer pairs.
     */
    private function cells(string $json): array
    {
        $cells = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        if (
            ! is_array($cells)
            || ! array_is_list($cells)
            || array_filter($cells, $this->isNotPair(...)) !== []
        ) {
            throw new UnexpectedValueException('A route row holds a list of [x, y] integer pairs.');
        }

        /**
         * Every entry was just checked to be an [x, y] pair of integers.
         *
         * @var list<array{int, int}> $cells
         */
        return $cells;
    }

    /**
     * Tells whether a decoded value is anything but an [x, y] pair of integers.
     */
    private function isNotPair(mixed $cell): bool
    {
        return ! is_array($cell)
            || ! array_is_list($cell)
            || count($cell) !== self::PAIR
            || ! is_int($cell[0])
            || ! is_int($cell[1]);
    }
}
