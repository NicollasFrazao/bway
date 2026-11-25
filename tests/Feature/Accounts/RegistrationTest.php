<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

/**
 * The sign-up form filled in for Ana, with any field replaced.
 *
 * @param  array<string, string>  $changes  The fields to replace.
 * @return array<string, string>
 */
function anaForm(array $changes = []): array
{
    return [
        'name' => 'Ana Souza',
        'email' => 'Ana@Example.com',
        'password' => 'correct horse',
        'password_confirmation' => 'correct horse',
        ...$changes,
    ];
}

it('shows the sign-up form with a form token', function (): void {
    get('/register')->assertOk()->assertSee('Create an account')->assertSee('name="_token"', false);
});

it('registers a shopper with a hashed password, signs them in and opens their list', function (): void {
    post('/register', anaForm())->assertRedirect('/list');

    $user = User::query()->where('email', 'ana@example.com')->sole();
    assertAuthenticatedAs($user);
    expect($user->role)->toBe('shopper')
        ->and($user->getAuthPassword())->not->toBe('correct horse')
        ->and(Hash::check('correct horse', $user->getAuthPassword()))->toBeTrue();
});

it('ignores a role sent with the form', function (): void {
    post('/register', anaForm(['role' => 'administrator']))->assertRedirect('/list');

    expect(User::query()->sole()->role)->toBe('shopper');
});

it('puts each refused value on its field', function (string $field, string $value, string $message): void {
    $changes = $field === 'password' ? ['password' => $value, 'password_confirmation' => $value] : [$field => $value];

    from('/register')->post('/register', anaForm($changes))
        ->assertRedirect('/register')
        ->assertSessionHasErrors([$field => $message]);

    assertGuest();
})->with([
    'bad email' => ['email', 'ana', 'Type a valid email address.'],
    'short password' => ['password', 'short', 'The password needs at least 8 characters and at most 72 bytes.'],
    'blank name' => ['name', ' ', 'The name field is required.'],
    'name too long' => ['name', str_repeat('a', 101), 'Type a name of up to 100 characters.'],
]);

it('refuses an email that has an account already', function (): void {
    User::factory()->create(['email' => 'ana@example.com']);

    post('/register', anaForm())->assertSessionHasErrors(['email' => 'An account with this email exists already.']);

    expect(User::query()->count())->toBe(1);
});

it('refuses a password typed differently the second time', function (): void {
    post('/register', anaForm(['password_confirmation' => 'other horse']))->assertSessionHasErrors('password');

    expect(User::query()->count())->toBe(0);
});

it('limits sign-ups to six a minute from one address', function (): void {
    foreach (range(1, 6) as $attempt) {
        post('/register', anaForm(['email' => "ana{$attempt}@example.com"]));
        post('/logout');
    }

    post('/register', anaForm(['email' => 'ana7@example.com']))->assertTooManyRequests();
});

it('sends a signed-in user away from the guest pages', function (string $page): void {
    actingAs(User::factory()->create())->get($page)->assertRedirect('/list');
})->with(['/register', '/login', '/forgot-password']);
