<?php

declare(strict_types=1);

use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteCache;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;
use App\Infrastructure\Storage\FilesystemRouteCache;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Domain\InMemoryRouteCache;

/*
 * The contract every RouteCache keeps. The in-memory adapter is the reference; the filesystem
 * adapter is the production one.
 */
dataset('route caches', [
    'in memory' => fn (): RouteCache => new InMemoryRouteCache,
    'filesystem' => fn (): RouteCache => new FilesystemRouteCache(Storage::fake('local')),
]);

/**
 * A route of the given cells.
 *
 * @param  array{int, int}  ...$cells  [x, y] pairs.
 */
function routeThrough(array ...$cells): Route
{
    return new Route(array_map(static fn (array $cell): GridCoordinate => new GridCoordinate($cell[0], $cell[1]), array_values($cells)));
}

it('gives back the route kept for an ordered pair of sectors', function (RouteCache $routes): void {
    $route = routeThrough([1, 1], [2, 1], [2, 2]);

    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), $route);

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(3)))->toEqual($route)
        ->and($routes->find(new StoreId(1), new SectorId(3), new SectorId(2)))->toBeNull()
        ->and($routes->find(new StoreId(2), new SectorId(2), new SectorId(3)))->toBeNull();
})->with('route caches');

it('keeps a route of a single cell, from a sector to itself', function (RouteCache $routes): void {
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(2), routeThrough([4, 4]));

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(2))?->length())->toBe(0);
})->with('route caches');

it('replaces the route kept for a pair', function (RouteCache $routes): void {
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), routeThrough([1, 1], [2, 1]));
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), routeThrough([1, 1], [1, 2]));

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(3)))->toEqual(routeThrough([1, 1], [1, 2]));
})->with('route caches');

it('forgets every route of one store and only of that store', function (RouteCache $routes): void {
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), routeThrough([1, 1]));
    $routes->remember(new StoreId(1), new SectorId(3), new SectorId(2), routeThrough([1, 1]));
    $routes->remember(new StoreId(2), new SectorId(2), new SectorId(3), routeThrough([1, 1]));

    $routes->forgetStore(new StoreId(1));

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(3)))->toBeNull()
        ->and($routes->find(new StoreId(1), new SectorId(3), new SectorId(2)))->toBeNull()
        ->and($routes->find(new StoreId(2), new SectorId(2), new SectorId(3)))->not->toBeNull();
})->with('route caches');

it('writes a route as [x, y] pairs under the store and the pair of sectors', function (): void {
    $disk = Storage::fake('local');

    new FilesystemRouteCache($disk)->remember(new StoreId(1), new SectorId(2), new SectorId(10), routeThrough([5, 6], [5, 7]));

    expect($disk->get('stores/1/routes/2-10.json'))->toBe('[[5,6],[5,7]]');
});

it('refuses a route file it did not write', function (string $contents): void {
    $disk = Storage::fake('local');
    $disk->put('stores/1/routes/2-3.json', $contents);

    expect(fn (): ?Route => new FilesystemRouteCache($disk)->find(new StoreId(1), new SectorId(2), new SectorId(3)))
        ->toThrow(UnexpectedValueException::class, 'A route file holds a list of [x, y] integer pairs.');
})->with([
    'not a list' => ['{"a": [1, 2]}'],
    'a cell that is not a pair' => ['[[1, 2, 3]]'],
    'a cell keyed by name' => ['[{"x": 1, "y": 2}]'],
    'a coordinate that is not an integer' => ['[[1, "2"]]'],
    'a scalar' => ['7'],
]);
