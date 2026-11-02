<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('stores a hash of the password, never the password itself', function (): void {
    $user = User::factory()->create(['password' => 'correct horse battery staple']);

    expect($user->getAuthPassword())->not->toBe('correct horse battery staple')
        ->and(Hash::check('correct horse battery staple', $user->getAuthPassword()))->toBeTrue();
});

it('keeps the password and the remember token out of serialisation', function (): void {
    $user = User::factory()->create();

    expect($user->toArray())->not->toHaveKeys(['password', 'remember_token']);
});

it('seeds a user to sign in with', function (): void {
    seed();

    assertDatabaseHas('users', ['email' => 'test@example.com']);
});
