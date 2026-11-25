<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Cookie;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\freezeSecond;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

/**
 * Ana, a shopper whose password is `correct horse`.
 */
function ana(): User
{
    return User::factory()->create(['name' => 'Ana Souza', 'email' => 'ana@example.com', 'password' => 'correct horse']);
}

/**
 * Signs in as Ana with a wrong password the given number of times.
 */
function failSignIns(int $times): void
{
    for ($attempt = 0; $attempt < $times; $attempt++) {
        post('/login', ['email' => 'ana@example.com', 'password' => 'wrong horse']);
    }
}

it('shows the sign-in form', function (): void {
    get('/login')->assertOk()->assertSee('Sign in')->assertSee('name="remember"', false);
});

it('signs in with the email in any case, renews the session and opens the list', function (): void {
    $user = ana();
    get('/login');
    $before = Session::getId();

    post('/login', ['email' => ' ANA@example.com', 'password' => 'correct horse'])->assertRedirect('/list');

    assertAuthenticatedAs($user);
    expect(Session::getId())->not->toBe($before);
});

it('goes back to the page the guest was sent away from', function (): void {
    ana();
    get('/admin/stores')->assertRedirect('/login');

    post('/login', ['email' => 'ana@example.com', 'password' => 'correct horse'])->assertRedirect('/admin/stores');
});

it('keeps the session with "keep me signed in"', function (): void {
    ana();

    $response = post('/login', ['email' => 'ana@example.com', 'password' => 'correct horse', 'remember' => '1']);

    $cookies = array_map(static fn (Cookie $cookie): string => $cookie->getName(), $response->headers->getCookies());
    expect(array_filter($cookies, static fn (string $name): bool => str_starts_with($name, 'remember_web_')))->not->toBeEmpty();
});

it('answers a wrong password and an unknown email the same way', function (string $email, string $password): void {
    ana();

    from('/login')->post('/login', ['email' => $email, 'password' => $password])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['email' => 'The email or the password is wrong.']);

    assertGuest();
})->with([
    'wrong password' => ['ana@example.com', 'wrong horse'],
    'unknown email' => ['bia@example.com', 'correct horse'],
    'malformed email' => ['ana', 'correct horse'],
]);

it('waits after five failed sign-ins for the email and address, even with the right password', function (): void {
    ana();

    freezeSecond();
    failSignIns(5);

    post('/login', ['email' => 'ana@example.com', 'password' => 'correct horse'])
        ->assertSessionHasErrors(['email' => 'Too many attempts. Try again in 60 seconds.']);

    assertGuest();
});

it('starts counting again after a successful sign-in', function (): void {
    ana();

    failSignIns(4);
    post('/login', ['email' => 'ana@example.com', 'password' => 'correct horse']);
    post('/logout');
    failSignIns(4);

    post('/login', ['email' => 'ana@example.com', 'password' => 'correct horse'])->assertRedirect('/list');
});

it('signs out, ends the session and goes home', function (): void {
    actingAs(ana())->post('/logout')->assertRedirect('/');

    assertGuest();
});
