<?php

declare(strict_types=1);

use App\Application\Visit\PlanVisit;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Grid\GridArea;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Domain\Store\Partition;
use App\Domain\Store\Sector;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreRepository;
use App\Domain\Visit\VisitPlan;
use App\Domain\Visit\VisitStop;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\RouteRecord;
use App\Infrastructure\Persistence\Eloquent\SectorProductRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use Database\Seeders\DemoShopperSeeder;
use Database\Seeders\ProductCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\V1StoreOne;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\seed;

/*
 * `php artisan migrate --seed` on an empty MySQL database: the catalogue, store 1 as v1 left it, and a
 * demo user whose list can be planned, shopped and planned again through the production bindings.
 */
uses(RefreshDatabase::class);

/**
 * The plan of the demo user's visit of store 1, through the use case.
 */
function demoVisit(): VisitPlan
{
    $user = User::query()->where('email', DemoShopperSeeder::EMAIL)->sole();

    return app(PlanVisit::class)->handle(new UserId($user->id), new StoreId(1));
}

/**
 * Each stop of a plan, as its sector id and the names of the products to pick up there.
 *
 * @return list<array{int, list<string>}>
 */
function demoStops(VisitPlan $plan): array
{
    return array_map(
        static fn (VisitStop $stop): array => [
            $stop->sector->id->value,
            array_map(static fn (ListItem $item): string => $item->productName->value, $stop->items),
        ],
        $plan->stops,
    );
}

/**
 * The areas of a store's sectors or partitions, keyed by id.
 *
 * @param  list<Sector>|list<Partition>  $parts  The sectors or the partitions of a store.
 * @return array<int, GridArea>
 */
function areasById(array $parts): array
{
    $areas = [];

    foreach ($parts as $part) {
        $areas[$part->id->value] = $part->area;
    }

    return $areas;
}

it('seeds the catalogue in English, each department but one sold in the sector of its name', function (): void {
    seed();

    $names = array_map(static fn (Product $product): string => $product->name->value, app(ProductCatalog::class)->search('rice'));
    $sectorNames = array_map(
        static fn (Sector $sector): string => $sector->name->value,
        app(StoreRepository::class)->get(new StoreId(1))->sectors(),
    );

    assertDatabaseCount('products', 202);
    assertDatabaseCount('sector_products', 199);
    expect($names)->toBe(['Brown rice', 'Rice'])
        ->and($sectorNames)->toBe(['Entrance', ...array_slice(array_keys(ProductCatalogSeeder::DEPARTMENTS), 0, 5)]);
});

it('seeds store 1 with the layout and the floor plan of v1, mapped to the grid PHP maps', function (): void {
    seed();

    $store = app(StoreRepository::class)->get(new StoreId(1));

    expect(areasById($store->sectors()))->toEqual(V1StoreOne::sectorAreas())
        ->and(areasById($store->partitions()))->toEqual(areasById(V1StoreOne::store()->partitions()))
        ->and($store->entrance()->id->value)->toBe(2)
        ->and(app(StoreGridRepository::class)->forStore(new StoreId(1)))->toEqual(V1StoreOne::mappedByPhp())
        ->and(file_get_contents(database_path('seeders/data/store-1/floor-plan.jpg')))->toBe(V1StoreOne::floorPlan());
});

it('seeds a demo user who plans, shops and plans again the visit of store 1', function (): void {
    seed();
    $user = User::query()->where('email', DemoShopperSeeder::EMAIL)->sole();

    $first = demoVisit();
    $lists = app(ShoppingListRepository::class);
    $list = $lists->forOwner(new UserId($user->id));
    $list->acquire(new ProductId(ProductRecord::query()->where('name', 'Bread loaf')->sole()->id));
    $lists->save($list);
    $second = demoVisit();

    expect(Hash::check('password', $user->getAuthPassword()))->toBeTrue()
        ->and(demoStops($first))->toBe([
            [3, ['Bread loaf']],
            [5, ['Dish soap']],
            [6, ['Rice']],
            [10, ['Soft drinks']],
            [11, ['Milk', 'Bananas']],
        ])
        ->and($first->length)->toBe(1193)
        ->and(array_map(static fn (ListItem $item): string => $item->productName->value, $first->unsold))->toBe(['Batteries'])
        ->and(array_column(demoStops($second), 0))->toBe([5, 6, 10, 11])
        ->and(RouteRecord::query()->count())->toBe(25);
});

it('seeds once: running the seeders again changes nothing', function (): void {
    seed();
    $counts = [ProductRecord::query()->count(), SectorProductRecord::query()->count(), User::query()->count()];

    seed();

    expect([ProductRecord::query()->count(), SectorProductRecord::query()->count(), User::query()->count()])->toBe($counts);
});
