<?php

declare(strict_types=1);

use App\Domain\Grid\Grid;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Infrastructure\Storage\FilesystemStoreGridRepository;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\GridDrawing;

/*
 * The contract every StoreGridRepository keeps. The in-memory adapter is the reference; the
 * filesystem adapter is the production one.
 */
dataset('store grid repositories', [
    'in memory' => fn (): StoreGridRepository => new InMemoryStoreGridRepository,
    'filesystem' => fn (): StoreGridRepository => new FilesystemStoreGridRepository(Storage::fake('local')),
]);

it('gives back the grid saved for a store', function (StoreGridRepository $grids): void {
    $grid = GridDrawing::grid(['#####', '#oSP#', '#..o#', '#####']);

    $grids->save(new StoreId(1), $grid);

    expect($grids->forStore(new StoreId(1)))->toEqual($grid);
})->with('store grid repositories');

it('keeps each store under its own id, where v1 wrote every store to the same file', function (StoreGridRepository $grids): void {
    $first = GridDrawing::grid(['##', '##']);
    $second = GridDrawing::grid(['###', '#o#', '###']);

    $grids->save(new StoreId(1), $first);
    $grids->save(new StoreId(2), $second);

    expect($grids->forStore(new StoreId(1)))->toEqual($first)
        ->and($grids->forStore(new StoreId(2)))->toEqual($second);
})->with('store grid repositories');

it('replaces the grid of a store mapped again', function (StoreGridRepository $grids): void {
    $grids->save(new StoreId(1), GridDrawing::grid(['##', '##']));
    $grids->save(new StoreId(1), GridDrawing::grid(['#o#']));

    expect($grids->forStore(new StoreId(1)))->toEqual(GridDrawing::grid(['#o#']));
})->with('store grid repositories');

it('reports a store whose floor plan has not been mapped', function (StoreGridRepository $grids): void {
    expect(fn (): Grid => $grids->forStore(new StoreId(3)))
        ->toThrow(GridNotMapped::class, 'The floor plan of store 3 has not been mapped yet.');
})->with('store grid repositories');

it('writes a grid as rows of cell codes under the store id', function (): void {
    $disk = Storage::fake('local');

    new FilesystemStoreGridRepository($disk)->save(new StoreId(7), GridDrawing::grid(['#oSP', '..##']));

    expect($disk->get('stores/7/grid.json'))->toBe('{"rows":["1423","0011"]}');
});

it('refuses a grid file it did not write', function (string $contents): void {
    $disk = Storage::fake('local');
    $disk->put('stores/1/grid.json', $contents);

    expect(fn (): Grid => new FilesystemStoreGridRepository($disk)->forStore(new StoreId(1)))
        ->toThrow(UnexpectedValueException::class, 'A grid file holds {"rows": [...]}, one string of cell codes per row.');
})->with([
    'not an object' => ['[1, 2]'],
    'rows missing' => ['{"cells": []}'],
    'rows not a list' => ['{"rows": {"a": "11"}}'],
    'a row that is not a string' => ['{"rows": [11]}'],
    'a row with letters' => ['{"rows": ["1x1"]}'],
]);
