<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Account that signs in to the app; the Eloquent model behind Laravel's session guard.
 *
 * It lives in Infrastructure because it is a persistence detail: the domain will get its own
 * account concepts when accounts are rebuilt (issue #8), and this model will map to them.
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
final class User extends Authenticatable
{
    /**
     * Builds users for tests and seeders through {@see UserFactory}.
     *
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    use Notifiable;

    /**
     * Casts the verification date to a date and hashes the password on assignment.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
