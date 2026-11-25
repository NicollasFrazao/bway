<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\travel;

uses(RefreshDatabase::class);

/**
 * The reset form filled in with a new password and the token given.
 *
 * @return array<string, string>
 */
function resetForm(string $token, string $password = 'new password'): array
{
    return ['token' => $token, 'email' => 'Ana@example.com', 'password' => $password, 'password_confirmation' => $password];
}

/**
 * Ana's stored password hash.
 */
function anaHash(User $user): string
{
    return User::query()->findOrFail($user->id)->getAuthPassword();
}

it('shows the forms', function (): void {
    get('/forgot-password')->assertOk()->assertSee('Forgot your password?');
    get('/reset-password/a-token?email=ana@example.com')->assertOk()->assertSee('value="ana@example.com"', false);
});

it('sends a reset link to an account, and answers the same for an unknown email', function (): void {
    Notification::fake();
    $user = User::factory()->create(['email' => 'ana@example.com']);
    $status = 'If an account uses this email, a link to reset the password was sent to it.';

    post('/forgot-password', ['email' => ' Ana@example.com'])->assertSessionHas('status', $status);
    post('/forgot-password', ['email' => 'bia@example.com'])->assertSessionHas('status', $status);

    Notification::assertSentTo($user, ResetPassword::class);
    Notification::assertSentTimes(ResetPassword::class, 1);
});

it('sets the new password with a valid link and ends the old "keep me signed in" cookies', function (): void {
    $user = User::factory()->create(['email' => 'ana@example.com']);
    $rememberToken = $user->getRememberToken();

    post('/reset-password', resetForm(Password::createToken($user)))->assertRedirect('/login');

    $user->refresh();
    expect(Hash::check('new password', $user->getAuthPassword()))->toBeTrue()
        ->and($user->getRememberToken())->not->toBe($rememberToken);
    post('/login', ['email' => 'ana@example.com', 'password' => 'new password'])->assertRedirect('/list');
});

it('refuses a forged token and a new password that is too short', function (): void {
    $user = User::factory()->create(['email' => 'ana@example.com']);
    $before = anaHash($user);

    post('/reset-password', resetForm('forged'))
        ->assertSessionHasErrors(['email' => 'This link is invalid or has expired. Ask for a new one.']);
    post('/reset-password', resetForm(Password::createToken($user), 'short'))
        ->assertSessionHasErrors(['password' => 'The password needs at least 8 characters and at most 72 bytes.']);

    expect(anaHash($user))->toBe($before);
});

it('refuses a link older than an hour', function (): void {
    $user = User::factory()->create(['email' => 'ana@example.com']);
    $token = Password::createToken($user);

    travel(61)->minutes();

    post('/reset-password', resetForm($token))->assertSessionHasErrors('email');
});
