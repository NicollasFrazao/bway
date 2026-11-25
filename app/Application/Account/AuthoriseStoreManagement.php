<?php

declare(strict_types=1);

namespace App\Application\Account;

use App\Domain\Account\AccountNotFound;
use App\Domain\Account\AccountRepository;
use App\Domain\Shared\UserId;

/**
 * Says whether an account may create and change stores (use case behind the `manage-stores` gate).
 */
final readonly class AuthoriseStoreManagement
{
    /**
     * Takes the accounts.
     */
    public function __construct(private AccountRepository $accounts) {}

    /**
     * Tells whether the account is an administrator.
     *
     * @throws AccountNotFound when no account has the id.
     */
    public function handle(UserId $id): bool
    {
        return $this->accounts->get($id)->canManageStores();
    }
}
