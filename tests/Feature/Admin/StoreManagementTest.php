<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use Database\Seeders\StoreWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CornerShop;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/*
 * B5's rule: store management only for administrators. The role is read from the account on every
 * request, never from the request.
 */
uses(RefreshDatabase::class);

it('lists the stores to an administrator', function (): void {
    (new StoreWriter)->store(CornerShop::store());

    actingAs(User::factory()->administrator()->create())->get('/admin/stores')
        ->assertOk()
        ->assertSee(CornerShop::store()->name->value)
        ->assertSee('Stores</a>', false);
});

it('says when there is no store yet', function (): void {
    actingAs(User::factory()->administrator()->create())->get('/admin/stores')->assertOk()->assertSee('No store yet.');
});

it('forbids store management to a shopper, whatever the request claims', function (): void {
    actingAs(User::factory()->create())
        ->get('/admin/stores?role=administrator', ['X-Role' => 'administrator'])
        ->assertForbidden();
});

it('hides the store management link from a shopper', function (): void {
    actingAs(User::factory()->create())->get('/list')->assertOk()->assertDontSee('/admin/stores');
});

it('sends a guest to the sign-in page', function (): void {
    get('/admin/stores')->assertRedirect('/login');
});
