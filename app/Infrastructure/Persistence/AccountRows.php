<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Account\Account;
use App\Domain\Account\Credentials;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\HashedPassword;
use App\Domain\Account\InvalidAccountData;
use App\Domain\Account\Role;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Domain\Shared\UserId;
use App\Infrastructure\Persistence\Eloquent\User;
use UnexpectedValueException;
use ValueError;

/**
 * Maps a row of `users` to the domain's account and back, for {@see EloquentAccountRepository}.
 */
final class AccountRows
{
    /**
     * Copies the name, the credentials and the role to the row.
     */
    public static function fill(User $user, Name $name, Credentials $credentials, Role $role): void
    {
        $user->forceFill([
            'name' => $name->value,
            'email' => $credentials->email->value,
            'password' => $credentials->password->value,
            'role' => $role->value,
        ]);
    }

    /**
     * The domain account a row stands for.
     *
     * @throws UnexpectedValueException when the row holds a value no sign-up writes.
     */
    public static function toAccount(User $user): Account
    {
        try {
            return new Account(
                new UserId($user->id),
                Name::from($user->name),
                new Credentials(EmailAddress::from($user->email), new HashedPassword($user->getAuthPassword())),
                Role::from($user->role),
            );
        } catch (InvalidAccountData|InvalidName|ValueError $invalid) {
            throw new UnexpectedValueException(
                "The account {$user->id} holds a value no sign-up writes.",
                $invalid->getCode(),
                previous: $invalid,
            );
        }
    }
}
