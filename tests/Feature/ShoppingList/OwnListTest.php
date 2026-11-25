<?php

declare(strict_types=1);

use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\EnforcedRequestForgery;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\call;
use function Pest\Laravel\get;
use function Pest\Laravel\instance;

/*
 * B5's rule: a user only sees and changes their own list. No route takes a user id, so the tests
 * send other users' ids every way v1 accepted them and check that only the session's list moves.
 */
uses(RefreshDatabase::class);

beforeEach(function (): void {
    ProductRecord::query()->forceCreate(['id' => 1, 'name' => 'Rice']);
    ProductRecord::query()->forceCreate(['id' => 2, 'name' => 'Beans']);
    ProductRecord::query()->forceCreate(['id' => 3, 'name' => 'Bananas']);
});

/**
 * The names on a user's list, in the order they were added.
 *
 * @return list<string>
 */
function listOf(User $user): array
{
    $list = app(ShoppingListRepository::class)->forOwner(new UserId($user->id));

    return array_map(static fn (ListItem $item): string => $item->productName->value, $list->items());
}

/**
 * Puts products on a user's list through the add route, signed in as that user.
 */
function stock(User $user, int ...$products): void
{
    foreach ($products as $product) {
        actingAs($user)->post('/list/items', ['product_id' => $product]);
    }
}

/**
 * Two shoppers, Ana and Bia.
 *
 * @return array{User, User}
 */
function anaAndBia(): array
{
    return [User::factory()->create(), User::factory()->create()];
}

it('sends a guest to the sign-in page from every list route', function (string $method, string $uri): void {
    call($method, $uri)->assertRedirect('/login');
})->with([
    ['GET', '/list'],
    ['DELETE', '/list'],
    ['POST', '/list/items'],
    ['DELETE', '/list/items/1'],
]);

it('shows the signed-in user\'s own list by name, and not another user\'s', function (): void {
    [$ana, $bia] = anaAndBia();
    stock($ana, 1, 3);
    stock($bia, 2);

    actingAs($ana)->get('/list')
        ->assertOk()
        ->assertSeeInOrder(['Bananas', 'Rice'])
        ->assertDontSee('Beans');
});

it('adds to the session\'s list whatever user id the form carries', function (): void {
    [$ana, $bia] = anaAndBia();

    actingAs($ana)->from('/list')
        ->post('/list/items', ['product_id' => 1, 'user_id' => $bia->id, 'usuario' => $bia->id])
        ->assertRedirect('/list');

    expect(listOf($ana))->toBe(['Rice'])
        ->and(listOf($bia))->toBe([]);
});

it('removes from and clears only the session\'s list, whatever the query names', function (): void {
    [$ana, $bia] = anaAndBia();
    stock($ana, 1, 2);
    stock($bia, 1, 2);

    actingAs($ana)->delete("/list/items/1?user_id={$bia->id}")->assertRedirect('/list');
    actingAs($ana)->delete("/list?user_id={$bia->id}")->assertRedirect('/list');

    expect(listOf($ana))->toBe([])
        ->and(listOf($bia))->toBe(['Rice', 'Beans']);
});

it('has no route that names a user, as v1 did', function (string $method, string $uri): void {
    [$ana, $bia] = anaAndBia();
    stock($bia, 1);

    actingAs($ana)->call($method, $uri)->assertNotFound();

    expect(listOf($bia))->toBe(['Rice']);
})->with([
    'v1 list' => ['GET', '/usuario/2/listaCompras/2'],
    'v1 clear' => ['DELETE', '/usuario/2/listaCompras/2'],
    'list by user' => ['GET', '/users/2/list'],
    'list item by user' => ['DELETE', '/list/2/items/1'],
]);

it('refuses a product that does not exist and one on the list already', function (): void {
    $ana = User::factory()->create();
    stock($ana, 1);

    actingAs($ana)->from('/list')->post('/list/items', ['product_id' => 99])
        ->assertSessionHasErrors(['product_id' => 'That product does not exist.']);
    actingAs($ana)->from('/list')->post('/list/items', ['product_id' => 1])
        ->assertSessionHasErrors(['product_id' => 'That product is on your list already.']);
    actingAs($ana)->post('/list/items', ['product_id' => 0])->assertSessionHasErrors('product_id');

    expect(listOf($ana))->toBe(['Rice']);
});

it('answers not found for a product that is not on the list', function (): void {
    actingAs(User::factory()->create())->delete('/list/items/1')->assertNotFound();
});

it('searches the catalogue from the list page', function (): void {
    actingAs(User::factory()->create())->get('/list?search=ba')
        ->assertOk()
        ->assertSee('Bananas')
        ->assertSee('name="product_id" value="3"', false)
        ->assertDontSee('name="product_id" value="1"', false);

    get('/list?search=zz')->assertSee('No product matches "zz".');
});

it('refuses a change to the list sent without the form token', function (): void {
    instance(PreventRequestForgery::class, new EnforcedRequestForgery(app(), app('encrypter')));
    $ana = User::factory()->create();

    actingAs($ana)->post('/list/items', ['product_id' => 1])->assertStatus(419);
    actingAs($ana)->withSession(['_token' => 'form-token'])
        ->post('/list/items', ['product_id' => 2, '_token' => 'form-token'])
        ->assertRedirect();

    expect(listOf($ana))->toBe(['Beans']);
});
