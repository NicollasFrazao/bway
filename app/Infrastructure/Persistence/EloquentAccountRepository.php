<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Account\Account;
use App\Domain\Account\AccountNotFound;
use App\Domain\Account\AccountRepository;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\NewAccount;
use App\Domain\Account\Role;
use App\Domain\Shared\UserId;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use UnexpectedValueException;

/**
 * Accounts kept in the framework's `users` table, so the session guard and the password broker read
 * the same rows.
 */
final readonly class EloquentAccountRepository implements AccountRepository
{
    /**
     * The length of a remember-me token, as the framework makes them.
     */
    private const int REMEMBER_TOKEN_LENGTH = 60;

    /**
     * Writes the new account's row as a shopper's.
     *
     * @throws EmailAlreadyRegistered when the unique index finds the address taken (a sign-up that
     *                                raced another one with the same address).
     */
    public function register(NewAccount $account): UserId
    {
        $user = new User;
        AccountRows::fill($user, $account->name, $account->credentials, Role::Shopper);

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            throw EmailAlreadyRegistered::create();
        }

        return new UserId($user->id);
    }

    /**
     * Loads the account with that id.
     *
     * @throws AccountNotFound when there is none.
     * @throws UnexpectedValueException when its row holds a value no sign-up writes.
     */
    public function get(UserId $id): Account
    {
        return AccountRows::toAccount(User::query()->find($id->value) ?? throw AccountNotFound::withId($id));
    }

    /**
     * Finds the account of that email address, if any.
     *
     * @throws UnexpectedValueException when its row holds a value no sign-up writes.
     */
    public function findByEmail(EmailAddress $email): ?Account
    {
        $user = User::query()->where('email', $email->value)->first();

        return $user === null ? null : AccountRows::toAccount($user);
    }

    /**
     * Writes the account; a new password also replaces the remember-me token, so a "keep me signed
     * in" cookie made with the old password stops working.
     *
     * @throws AccountNotFound when no row has the account's id.
     */
    public function save(Account $account): void
    {
        $user = User::query()->find($account->id->value) ?? throw AccountNotFound::withId($account->id);
        AccountRows::fill($user, $account->name, $account->credentials, $account->role);

        if ($user->isDirty('password')) {
            $user->setRememberToken(Str::random(self::REMEMBER_TOKEN_LENGTH));
        }

        $user->save();
    }
}
