<?php

declare(strict_types=1);

use App\Domain\Grid\Grid;
use App\Domain\Shared\Name;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\StoreGridRecord;
use App\Infrastructure\Persistence\EloquentStoreGridRepository;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\GridDrawing;
use Tests\Support\StoreLayout;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    new StoreWriter()->store(new Store(new StoreId(1), Name::from('Downtown'), [StoreLayout::entrance(1)], []));
});

it('stores a grid as its size and its cell codes, row after row, one row per store', function (): void {
    $grids = new EloquentStoreGridRepository;

    $grids->save(new StoreId(1), GridDrawing::grid(['####']));
    $grids->save(new StoreId(1), GridDrawing::grid(['#oSP', '..##']));

    assertDatabaseCount('store_grids', 1);
    assertDatabaseHas('store_grids', ['store_id' => 1, 'width' => 4, 'height' => 2, 'cells' => '14230011']);
});

it('refuses a grid row it did not write', function (int $width, int $height, string $cells): void {
    StoreGridRecord::query()->create(['store_id' => 1, 'width' => $width, 'height' => $height, 'cells' => $cells]);

    expect(fn (): Grid => new EloquentStoreGridRepository()->forStore(new StoreId(1)))
        ->toThrow(UnexpectedValueException::class, "The grid of store 1 does not hold {$width} x {$height} cell codes from 0 to 4.");
})->with([
    'fewer codes than cells' => [2, 2, '111'],
    'more codes than cells' => [2, 2, '11111'],
    'a code no cell kind has' => [2, 1, '15'],
    'a letter' => [2, 1, '1x'],
    'no column' => [0, 1, ''],
]);
