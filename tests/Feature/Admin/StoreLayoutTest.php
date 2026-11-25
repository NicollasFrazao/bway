<?php

declare(strict_types=1);

use App\Application\Routing\GenerateStoreRoutes;
use App\Infrastructure\Persistence\Eloquent\PartitionRecord;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\RouteRecord;
use App\Infrastructure\Persistence\Eloquent\SectorProductRecord;
use App\Infrastructure\Persistence\Eloquent\SectorRecord;
use App\Infrastructure\Persistence\Eloquent\StoreGridRecord;
use App\Infrastructure\Persistence\Eloquent\StoreRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Queue\GenerateStoreRoutesJob;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\CornerShop;
use Tests\Support\PngFloorPlan;

use function Pest\Laravel\actingAs;

/*
 * B7's admin flow: a store set up from an image without touching the database by hand, with the
 * routes found by a queued job (the sync queue of the tests) and its errors on the page.
 */
uses(RefreshDatabase::class);

/**
 * An administrator.
 */
function administrator(): User
{
    return User::factory()->administrator()->create();
}

/**
 * Creates the U shop as the administrator, with the entrance at (1, 1).
 *
 * @param  array<array-key, mixed>  $changes  Fields to replace.
 * @return TestResponse<Response>
 */
function createUShop(User $admin, array $changes = []): TestResponse
{
    return actingAs($admin)->from('/admin/stores/create')->post('/admin/stores', [
        'name' => 'U shop',
        'floor_plan' => UploadedFile::fake()->createWithContent('u-shop.png', PngFloorPlan::uShop()),
        'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1,
        ...$changes,
    ]);
}

it('creates a store from its floor plan and its entrance, and maps the plan', function (): void {
    $admin = administrator();

    $response = createUShop($admin);

    $store = StoreRecord::query()->sole();
    $response->assertRedirect("/admin/stores/{$store->id}");
    expect($store->name)->toBe('U shop')
        ->and(SectorRecord::query()->sole()->name)->toBe('Entrance')
        ->and(StoreGridRecord::query()->where('store_id', $store->id)->exists())->toBeTrue();
    actingAs($admin)->get("/admin/stores/{$store->id}")
        ->assertOk()
        ->assertSee('The grid is 7 × 7 cells')
        ->assertSee("/admin/stores/{$store->id}/grid.png", false);
    actingAs($admin)->get("/admin/stores/{$store->id}/grid.png")->assertOk()->assertHeader('Content-Type', 'image/png');
});

it('refuses a plan that is not an image, one without a wall frame, and an entrance off the plan, writing nothing', function (array $changes, string $field, string $message): void {
    createUShop(administrator(), $changes)->assertSessionHasErrors([$field => $message]);

    expect(StoreRecord::query()->count())->toBe(0);
})->with([
    'not an image' => [['floor_plan' => UploadedFile::fake()->createWithContent('plan.png', 'not a picture')], 'floor_plan', 'This file is not an image that can be read.'],
    'no wall frame' => [['floor_plan' => UploadedFile::fake()->createWithContent('plan.png', PngFloorPlan::of('OOO', 'OOO'))], 'floor_plan', 'No red wall frames the store on this plan.'],
    'entrance off the plan' => [['x' => 6, 'width' => 3], 'area', 'This rectangle reaches beyond the mapped plan.'],
    'empty entrance' => [['width' => 0], 'area', 'Use a width and a height of at least 1 cell.'],
]);

it('lays out sectors and partitions, refusing an overlap and removing the entrance', function (): void {
    $admin = administrator();
    createUShop($admin);
    $store = StoreRecord::query()->sole()->id;
    $entrance = SectorRecord::query()->sole()->id;

    actingAs($admin)->post("/admin/stores/{$store}/sectors", ['name' => 'Bakery', 'x' => 5, 'y' => 5, 'width' => 1, 'height' => 1])
        ->assertRedirect("/admin/stores/{$store}");
    $bakery = SectorRecord::query()->where('name', 'Bakery')->sole()->id;
    actingAs($admin)->put("/admin/stores/{$store}/sectors/{$bakery}", ['name' => 'Bread', 'x' => 5, 'y' => 4, 'width' => 1, 'height' => 1]);
    actingAs($admin)->post("/admin/stores/{$store}/partitions", ['x' => 3, 'y' => 3, 'width' => 1, 'height' => 1]);
    actingAs($admin)->from("/admin/stores/{$store}")
        ->post("/admin/stores/{$store}/sectors", ['name' => 'Clash', 'x' => 3, 'y' => 3, 'width' => 1, 'height' => 1])
        ->assertSessionHasErrors(['area' => 'This rectangle overlaps another sector or a partition.']);
    actingAs($admin)->from("/admin/stores/{$store}")->delete("/admin/stores/{$store}/sectors/{$entrance}")
        ->assertSessionHasErrors(['area' => 'The entrance cannot be removed; move it instead.']);

    actingAs($admin)->get("/admin/stores/{$store}")->assertSee('value="Bread"', false)->assertDontSee('Clash');
    expect(SectorRecord::query()->count())->toBe(2);
});

it('places products in sectors', function (): void {
    $admin = administrator();
    createUShop($admin);
    $store = StoreRecord::query()->sole()->id;
    actingAs($admin)->post("/admin/stores/{$store}/sectors", ['name' => 'Bakery', 'x' => 5, 'y' => 5, 'width' => 1, 'height' => 1]);
    $bakery = SectorRecord::query()->where('name', 'Bakery')->sole()->id;
    $bread = ProductRecord::query()->forceCreate(['name' => 'Bread'])->id;
    $milk = ProductRecord::query()->forceCreate(['name' => 'Milk'])->id;

    actingAs($admin)->get("/admin/stores/{$store}/assortment")->assertOk()->assertSee('Bread')->assertSee('Not sold');
    actingAs($admin)->put("/admin/stores/{$store}/assortment", ['sectors' => [$bread => $bakery, $milk => '']])
        ->assertRedirect("/admin/stores/{$store}");
    actingAs($admin)->from("/admin/stores/{$store}/assortment")
        ->put("/admin/stores/{$store}/assortment", ['sectors' => [$bread => 999]])
        ->assertSessionHasErrors('sectors');

    expect(SectorProductRecord::query()->where('store_id', $store)->pluck('product_id')->all())->toBe([$bread]);
});

it('finds every route on the queue and shows the sectors no walkway reaches', function (): void {
    $admin = administrator();
    createUShop($admin);
    $store = StoreRecord::query()->sole()->id;
    actingAs($admin)->post("/admin/stores/{$store}/sectors", ['name' => 'Corner', 'x' => 5, 'y' => 5, 'width' => 1, 'height' => 1]);
    actingAs($admin)->post("/admin/stores/{$store}/sectors", ['name' => 'Island', 'x' => 3, 'y' => 3, 'width' => 1, 'height' => 1]);

    actingAs($admin)->get("/admin/stores/{$store}")->assertSee('0 of 6 routes found.');
    actingAs($admin)->post("/admin/stores/{$store}/routes")->assertRedirect("/admin/stores/{$store}");

    actingAs($admin)->get("/admin/stores/{$store}")
        ->assertSee('2 of 6 routes found.')
        ->assertSee('Every pair of sectors has been searched.')
        ->assertSee('Entrance → Island')
        ->assertSee('Island → Corner');
    expect(RouteRecord::query()->count())->toBe(2);
});

it('maps a new plan, forgetting the routes, and refuses one the layout does not fit', function (): void {
    $admin = administrator();
    createUShop($admin);
    $store = StoreRecord::query()->sole()->id;
    actingAs($admin)->post("/admin/stores/{$store}/sectors", ['name' => 'Corner', 'x' => 5, 'y' => 5, 'width' => 1, 'height' => 1]);
    actingAs($admin)->post("/admin/stores/{$store}/routes");

    actingAs($admin)->post("/admin/stores/{$store}/floor-plan", ['floor_plan' => UploadedFile::fake()->createWithContent('u.png', PngFloorPlan::uShop())])
        ->assertRedirect("/admin/stores/{$store}");
    expect(RouteRecord::query()->count())->toBe(0);

    actingAs($admin)->from("/admin/stores/{$store}")
        ->post("/admin/stores/{$store}/floor-plan", ['floor_plan' => UploadedFile::fake()->createWithContent('small.png', PngFloorPlan::of('RRR', 'ROR', 'RRR'))])
        ->assertSessionHasErrors(['floor_plan' => 'The layout reaches beyond this plan; move the sectors first.']);
});

it('answers not found for a store, a sector or a grid that does not exist', function (string $method, string $uri): void {
    actingAs(administrator())->call($method, $uri, ['name' => 'X', 'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1])->assertNotFound();
})->with([
    ['GET', '/admin/stores/9'],
    ['GET', '/admin/stores/9/grid.png'],
    ['GET', '/admin/stores/9/assortment'],
    ['POST', '/admin/stores/9/sectors'],
    ['POST', '/admin/stores/9/partitions'],
    ['POST', '/admin/stores/9/routes'],
]);

it('keeps every management page and action to administrators', function (string $method, string $uri): void {
    actingAs(User::factory()->create())->call($method, $uri)->assertForbidden();
})->with([
    ['GET', '/admin/stores/create'],
    ['POST', '/admin/stores'],
    ['GET', '/admin/stores/1'],
    ['POST', '/admin/stores/1/sectors'],
    ['POST', '/admin/stores/1/routes'],
    ['PUT', '/admin/stores/1/assortment'],
]);

it('removes a partition and answers not found for a sector that is not the store\'s', function (): void {
    $admin = administrator();
    createUShop($admin);
    $store = StoreRecord::query()->sole()->id;
    actingAs($admin)->post("/admin/stores/{$store}/partitions", ['x' => 3, 'y' => 3, 'width' => 1, 'height' => 1]);
    $partition = PartitionRecord::query()->sole()->id;

    actingAs($admin)->delete("/admin/stores/{$store}/partitions/{$partition}")->assertRedirect("/admin/stores/{$store}");
    actingAs($admin)->delete("/admin/stores/{$store}/sectors/999")->assertNotFound();
    actingAs($admin)->put("/admin/stores/{$store}/sectors/999", ['name' => 'X', 'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1])->assertNotFound();
    actingAs($admin)->delete('/admin/stores/999/partitions/1')->assertNotFound();
    actingAs($admin)->from("/admin/stores/{$store}")
        ->put("/admin/stores/{$store}/sectors/".SectorRecord::query()->sole()->id, ['name' => str_repeat('a', 101), 'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1])
        ->assertSessionHasErrors(['name' => 'Type a name of up to 100 characters.']);
    actingAs($admin)->from("/admin/stores/{$store}")
        ->post("/admin/stores/{$store}/partitions", ['x' => 1, 'y' => 1, 'width' => 0, 'height' => 1])
        ->assertSessionHasErrors(['area' => 'Use a width and a height of at least 1 cell.']);

    expect(PartitionRecord::query()->count())->toBe(0);
});

it('shows a store whose plan is not mapped, and refuses a new plan that is not one', function (): void {
    (new StoreWriter)->store(CornerShop::store());
    $admin = administrator();

    actingAs($admin)->get('/admin/stores/1')->assertOk()->assertSee('This store\'s floor plan is not mapped yet.');
    actingAs($admin)->from('/admin/stores/1')
        ->post('/admin/stores/1/floor-plan', ['floor_plan' => UploadedFile::fake()->createWithContent('plan.png', 'not a picture')])
        ->assertSessionHasErrors(['floor_plan' => 'This file is not an image that can be read.']);
    actingAs($admin)->post('/admin/stores/9/floor-plan', ['floor_plan' => UploadedFile::fake()->createWithContent('u.png', PngFloorPlan::uShop())])
        ->assertNotFound();
});

it('lets the queued job end quietly for a store deleted meanwhile', function (): void {
    new GenerateStoreRoutesJob(99)->handle(app(GenerateStoreRoutes::class));

    expect(RouteRecord::query()->count())->toBe(0);
});
