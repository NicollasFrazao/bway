<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteCache;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use Illuminate\Contracts\Filesystem\Filesystem;
use UnexpectedValueException;

/**
 * Keeps each route as `stores/<id>/routes/<from>-<to>.json` on a Laravel disk: the cells as [x, y]
 * pairs, from the start to the target. It replaces v1's `rotas/<store>-<from>-<to>.json` files.
 */
final readonly class FilesystemRouteCache implements RouteCache
{
    /**
     * The values of one cell in a route file: x and y.
     */
    private const int PAIR = 2;

    /**
     * Takes the disk the routes are kept on.
     */
    public function __construct(private Filesystem $disk) {}

    /**
     * Reads the route kept for the pair, if any.
     *
     * @throws UnexpectedValueException when the file is not a route this class wrote.
     */
    public function find(StoreId $storeId, SectorId $from, SectorId $to): ?Route
    {
        $path = $this->path($storeId, $from, $to);

        if (! $this->disk->exists($path)) {
            return null;
        }

        return new Route(array_map(
            static fn (array $cell): GridCoordinate => new GridCoordinate($cell[0], $cell[1]),
            $this->cells((string) $this->disk->get($path)),
        ));
    }

    /**
     * Writes the route for the pair, replacing any kept before.
     */
    public function remember(StoreId $storeId, SectorId $from, SectorId $to, Route $route): void
    {
        $cells = array_map(static fn (GridCoordinate $cell): array => [$cell->x, $cell->y], $route->cells);

        $this->disk->put($this->path($storeId, $from, $to), json_encode($cells, JSON_THROW_ON_ERROR));
    }

    /**
     * Deletes the store's routes directory.
     */
    public function forgetStore(StoreId $storeId): void
    {
        $this->disk->deleteDirectory($this->directory($storeId));
    }

    /**
     * The [x, y] pairs of a route file.
     *
     * @return list<array{int, int}>
     *
     * @throws UnexpectedValueException when the file is not a JSON list of [x, y] integer pairs.
     */
    private function cells(string $contents): array
    {
        $cells = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (
            ! is_array($cells)
            || ! array_is_list($cells)
            || array_filter($cells, $this->isNotPair(...)) !== []
        ) {
            throw new UnexpectedValueException('A route file holds a list of [x, y] integer pairs.');
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

    /**
     * Where the store's routes are kept on the disk.
     */
    private function directory(StoreId $storeId): string
    {
        return "stores/{$storeId->value}/routes";
    }

    /**
     * Where the route for the pair is kept on the disk.
     */
    private function path(StoreId $storeId, SectorId $from, SectorId $to): string
    {
        return "{$this->directory($storeId)}/{$from->value}-{$to->value}.json";
    }
}
