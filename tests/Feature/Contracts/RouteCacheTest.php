<?php

declare(strict_types=1);

use App\Domain\Routing\RouteCache;
use App\Domain\Shared\Name;
use App\Domain\Store\SectorId;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\EloquentRouteCache;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Domain\InMemoryRouteCache;
use Tests\Support\Routes;
use Tests\Support\StoreLayout;

uses(RefreshDatabase::class);

/*
 * The contract every RouteCache keeps, given store 1 with sectors 2 and 3 and store 2 with sectors 4
 * and 5. The in-memory adapter is the reference; the Eloquent adapter runs it on MySQL.
 */
dataset('route caches', [
    'in memory' => fn (): RouteCache => new InMemoryRouteCache,
    'eloquent' => function (): RouteCache {
        $writer = new StoreWriter;
        $writer->store(new Store(new StoreId(1), Name::from('Downtown'), [StoreLayout::entrance(2), StoreLayout::sector(3)], []));
        $writer->store(new Store(new StoreId(2), Name::from('Harbour'), [StoreLayout::entrance(4), StoreLayout::sector(5)], []));

        return new EloquentRouteCache;
    },
]);

it('gives back the route kept for an ordered pair of sectors', function (RouteCache $routes): void {
    $route = Routes::through([1, 1], [2, 1], [2, 2]);

    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), $route);

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(3)))->toEqual($route)
        ->and($routes->find(new StoreId(1), new SectorId(3), new SectorId(2)))->toBeNull()
        ->and($routes->find(new StoreId(2), new SectorId(4), new SectorId(5)))->toBeNull();
})->with('route caches');

it('keeps a route of a single cell, from a sector to itself', function (RouteCache $routes): void {
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(2), Routes::through([4, 4]));

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(2))?->length())->toBe(0);
})->with('route caches');

it('replaces the route kept for a pair', function (RouteCache $routes): void {
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), Routes::through([1, 1], [2, 1]));
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), Routes::through([1, 1], [1, 2]));

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(3)))->toEqual(Routes::through([1, 1], [1, 2]));
})->with('route caches');

it('forgets every route of one store and only of that store', function (RouteCache $routes): void {
    $routes->remember(new StoreId(1), new SectorId(2), new SectorId(3), Routes::through([1, 1]));
    $routes->remember(new StoreId(1), new SectorId(3), new SectorId(2), Routes::through([1, 1]));
    $routes->remember(new StoreId(2), new SectorId(4), new SectorId(5), Routes::through([1, 1]));

    $routes->forgetStore(new StoreId(1));

    expect($routes->find(new StoreId(1), new SectorId(2), new SectorId(3)))->toBeNull()
        ->and($routes->find(new StoreId(1), new SectorId(3), new SectorId(2)))->toBeNull()
        ->and($routes->find(new StoreId(2), new SectorId(4), new SectorId(5)))->not->toBeNull();
})->with('route caches');
