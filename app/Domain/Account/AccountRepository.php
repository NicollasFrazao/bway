<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\UserId;

/**
 * Where accounts are kept (port; implemented in Infrastructure).
 */
interface AccountRepository
{
    /**
     * Stores a new shopper's account and gives it its id.
     *
     * @throws EmailAlreadyRegistered when another account has the email address.
     */
    public function register(NewAccount $account): UserId;

    /**
     * Loads one account.
     *
     * @throws AccountNotFound when no account has that id.
     */
    public function get(UserId $id): Account;

    /**
     * Finds the account of an email address.
     *
     * @return Account|null Null when no account has the address.
     */
    public function findByEmail(EmailAddress $email): ?Account;

    /**
     * Stores the account as it is now.
     *
     * @throws AccountNotFound when no account has its id.
     */
    public function save(Account $account): void;
}
