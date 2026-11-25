<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Account\Role;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Builds verified shoppers with fake names and e-mails and the password `password`.
 *
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /** Length of the fake remember-me token. */
    private const int REMEMBER_TOKEN_LENGTH = 10;

    /** The hash of `password`, computed once per process because hashing is slow on purpose. */
    private static ?string $passwordHash = null;

    /**
     * The attributes of a new user.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => self::$passwordHash ??= Hash::make('password'),
            'remember_token' => Str::random(self::REMEMBER_TOKEN_LENGTH),
            'role' => Role::Shopper->value,
        ];
    }

    /**
     * A user who may manage the stores.
     */
    public function administrator(): self
    {
        return $this->state(['role' => Role::Administrator->value]);
    }
}
