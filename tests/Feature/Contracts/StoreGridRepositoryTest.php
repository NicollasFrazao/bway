<?php

declare(strict_types=1);

use App\Domain\Grid\Grid;
use App\Domain\Shared\Name;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\Store;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\EloquentStoreGridRepository;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\GridDrawing;
use Tests\Support\StoreLayout;

uses(RefreshDatabase::class);

/*
 * The contract every StoreGridRepository keeps, given stores 1 and 2. The in-memory adapter is the
 * reference; the Eloquent adapter runs it on MySQL.
 */
dataset('store grid repositories', [
    'in memory' => fn (): StoreGridRepository => new InMemoryStoreGridRepository,
    'eloquent' => function (): StoreGridRepository {
        $writer = new StoreWriter;
        $writer->store(new Store(new StoreId(1), Name::from('Downtown'), [StoreLayout::entrance(1)], []));
        $writer->store(new Store(new StoreId(2), Name::from('Harbour'), [StoreLayout::entrance(2)], []));

        return new EloquentStoreGridRepository;
    },
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
    expect(fn (): Grid => $grids->forStore(new StoreId(2)))
        ->toThrow(GridNotMapped::class, 'The floor plan of store 2 has not been mapped yet.');
})->with('store grid repositories');
