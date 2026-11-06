<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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
