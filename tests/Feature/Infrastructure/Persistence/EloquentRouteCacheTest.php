<?php

declare(strict_types=1);

use App\Domain\Routing\Route;
use App\Domain\Shared\Name;
use App\Domain\Store\SectorId;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\RouteRecord;
use App\Infrastructure\Persistence\EloquentRouteCache;
use Database\Seeders\StoreWriter;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Routes;
use Tests\Support\StoreLayout;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $writer = new StoreWriter;
    $writer->store(new Store(new StoreId(1), Name::from('Downtown'), [StoreLayout::entrance(2), StoreLayout::sector(3)], []));
    $writer->store(new Store(new StoreId(2), Name::from('Harbour'), [StoreLayout::entrance(4)], []));
});

it('stores a route as a JSON list of [x, y] pairs under the store and the pair of sectors', function (): void {
    new EloquentRouteCache()->remember(new StoreId(1), new SectorId(2), new SectorId(3), Routes::through([5, 6], [5, 7]));

    $record = RouteRecord::query()->where(['store_id' => 1, 'from_sector_id' => 2, 'to_sector_id' => 3])->sole();

    expect(json_decode($record->cells, true))->toBe([[5, 6], [5, 7]]);
});

it('refuses a route between sectors of different stores', function (): void {
    expect(fn () => new EloquentRouteCache()->remember(new StoreId(1), new SectorId(2), new SectorId(4), Routes::through([1, 1])))
        ->toThrow(QueryException::class);
});

it('refuses a route row it did not write', function (string $cells): void {
    RouteRecord::query()->create(['store_id' => 1, 'from_sector_id' => 2, 'to_sector_id' => 3, 'cells' => $cells]);

    expect(fn (): ?Route => new EloquentRouteCache()->find(new StoreId(1), new SectorId(2), new SectorId(3)))
        ->toThrow(UnexpectedValueException::class, 'A route row holds a list of [x, y] integer pairs.');
})->with([
    'not a list' => ['{"a": [1, 2]}'],
    'a cell that is not a pair' => ['[[1, 2, 3]]'],
    'a cell keyed by name' => ['[{"x": 1, "y": 2}]'],
    'a coordinate that is not an integer' => ['[[1, "2"]]'],
    'a scalar' => ['7'],
]);
