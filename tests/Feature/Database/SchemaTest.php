<?php

declare(strict_types=1);

use App\Domain\Shared\Name;
use App\Domain\Store\SectorKind;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\RouteRecord;
use App\Infrastructure\Persistence\Eloquent\SectorProductRecord;
use App\Infrastructure\Persistence\Eloquent\SectorRecord;
use App\Infrastructure\Persistence\Eloquent\ShoppingListItemRecord;
use App\Infrastructure\Persistence\Eloquent\ShoppingListRecord;
use App\Infrastructure\Persistence\Eloquent\StoreGridRecord;
use App\Infrastructure\Persistence\Eloquent\StoreRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use Database\Seeders\StoreWriter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreLayout;

use function Pest\Laravel\assertDatabaseCount;

/*
 * The constraints of the MySQL schema that repeat a rule of the domain, so a row the domain would
 * refuse to load cannot be written by anything that skips the domain. Given store 1 (entrance 1,
 * sectors 2 and 3), store 2 (entrance 4) and product 7.
 */
uses(RefreshDatabase::class);

beforeEach(function (): void {
    $writer = new StoreWriter;
    $writer->store(new Store(new StoreId(1), Name::from('Downtown'), [StoreLayout::entrance(1), StoreLayout::sector(2), StoreLayout::sector(3)], [StoreLayout::partition(1)]));
    $writer->store(new Store(new StoreId(2), Name::from('Harbour'), [StoreLayout::entrance(4)], []));
    ProductRecord::query()->forceCreate(['id' => 7, 'name' => 'Rice']);
});

it('refuses a second entrance in a store, as the Store aggregate does', function (): void {
    expect(fn (): Model => SectorRecord::query()->create([
        'store_id' => 1, 'name' => 'Back door', 'kind' => SectorKind::Entrance, 'x' => 20, 'y' => 20, 'width' => 2, 'height' => 2,
    ]))->toThrow(QueryException::class, 'entrance_of_store_id');
});

it('refuses a product in two sectors of a store, as the Assortment does', function (): void {
    SectorProductRecord::query()->create(['store_id' => 1, 'sector_id' => 2, 'product_id' => 7]);

    expect(fn (): Model => SectorProductRecord::query()->create(['store_id' => 1, 'sector_id' => 3, 'product_id' => 7]))
        ->toThrow(QueryException::class, 'Duplicate entry');
});

it('refuses a product sold in a sector of another store', function (): void {
    expect(fn (): Model => SectorProductRecord::query()->create(['store_id' => 2, 'sector_id' => 2, 'product_id' => 7]))
        ->toThrow(QueryException::class, 'foreign key constraint fails');
});

it('refuses a product twice on a list, as the ShoppingList does', function (): void {
    $user = User::factory()->create();
    $list = ShoppingListRecord::query()->create(['user_id' => $user->id]);
    ShoppingListItemRecord::query()->create(['shopping_list_id' => $list->id, 'position' => 0, 'product_id' => 7, 'product_name' => 'Rice']);

    expect(fn (): Model => ShoppingListItemRecord::query()->create(['shopping_list_id' => $list->id, 'position' => 1, 'product_id' => 7, 'product_name' => 'Rice']))
        ->toThrow(QueryException::class, 'Duplicate entry');
});

it('deletes the layout, the assortment, the grid and the routes with their store', function (): void {
    SectorProductRecord::query()->create(['store_id' => 1, 'sector_id' => 2, 'product_id' => 7]);
    StoreGridRecord::query()->create(['store_id' => 1, 'width' => 1, 'height' => 1, 'cells' => '4']);
    RouteRecord::query()->create(['store_id' => 1, 'from_sector_id' => 2, 'to_sector_id' => 3, 'cells' => '[[0, 0]]']);

    StoreRecord::query()->whereKey(1)->delete();

    assertDatabaseCount('sectors', 1);
    assertDatabaseCount('partitions', 0);
    assertDatabaseCount('sector_products', 0);
    assertDatabaseCount('store_grids', 0);
    assertDatabaseCount('routes', 0);
});
