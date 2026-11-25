<?php

declare(strict_types=1);

use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Infrastructure\Persistence\Eloquent\User;
use Database\Seeders\DemoShopperSeeder;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CornerShop;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
 * B6's main flow on the seeded data: the demo shopper builds a list, picks store 1 and follows the
 * route drawn on its plan.
 */
uses(RefreshDatabase::class);

/**
 * The demo shopper of the seed, with the list of DemoShopperSeeder.
 */
function demoShopper(): User
{
    seed();

    return User::query()->where('email', DemoShopperSeeder::EMAIL)->sole();
}

it('lists the stores to choose from', function (): void {
    actingAs(demoShopper())->get('/stores')
        ->assertOk()
        ->assertSee('Demo supermarket')
        ->assertSee('/stores/1/visit', false);
});

it('shows the visit: the sectors in walking order with their items, and what the store does not sell', function (): void {
    actingAs(demoShopper())->get('/stores/1/visit')
        ->assertOk()
        ->assertSee('5 sectors to visit from the Entrance, 1193 steps in all.')
        ->assertSeeInOrder(['Bread loaf', 'Dish soap', 'Rice', 'Soft drinks', 'Milk', 'Bananas', 'Not sold in this store', 'Batteries']);
});

it('takes an acquired item off the list and plans the visit again without it', function (): void {
    $user = demoShopper();
    $items = app(ShoppingListRepository::class)->forOwner(new UserId($user->id))->items();
    $bread = ($items[1] ?? throw new LogicException('The demo list has a second item.'))->productId->value;

    actingAs($user)->post("/stores/1/visit/items/{$bread}")
        ->assertRedirect('/stores/1/visit')
        ->assertSessionHas('status', 'Bread loaf is off your list.');

    actingAs($user)->get('/stores/1/visit')->assertSee('4 sectors to visit')->assertDontSee('aria-label="Got Bread loaf"', false);
    actingAs($user)->post("/stores/1/visit/items/{$bread}")->assertNotFound();
});

it('draws the route on the plan, one walk highlighted at a time', function (): void {
    $user = demoShopper();

    $first = actingAs($user)->get('/stores/1/map')->assertOk();
    $third = actingAs($user)->get('/stores/1/map?leg=3')->assertOk();

    $first->assertSee('Walk 1 of 5: from Entrance to', false)
        ->assertSee('<svg viewBox="0 0 ', false)
        ->assertSee('role="img"', false)
        ->assertSee('Next walk')
        ->assertDontSee('Previous walk');
    $svg = (string) $first->baseResponse->getContent();
    expect(substr_count($svg, '<polyline'))->toBe(5)
        ->and(substr_count($svg, 'stroke="#c2410c"'))->toBe(1);
    $third->assertSee('Walk 3 of 5')->assertSee('Previous walk');
    actingAs($user)->get('/stores/1/map?leg=99')->assertSee('Walk 5 of 5');
});

it('says when a store\'s plan is not mapped yet, and when it sells nothing on the list', function (): void {
    (new StoreWriter)->store(CornerShop::store());
    $user = User::factory()->create();

    actingAs($user)->get('/stores/1/map')->assertOk()->assertSee('The map of this store is not ready yet');
    actingAs($user)->get('/stores/1/visit')->assertOk()->assertSee('Nothing on your list is sold in this store.');
});

it('answers not found for a store that does not exist', function (string $uri): void {
    actingAs(User::factory()->create())->get($uri)->assertNotFound();
})->with(['/stores/99/visit', '/stores/99/map', '/stores/0/visit', '/stores/abc/map']);

it('sends a guest to sign in', function (string $uri): void {
    get($uri)->assertRedirect('/login');
})->with(['/stores', '/stores/1/visit', '/stores/1/map']);
