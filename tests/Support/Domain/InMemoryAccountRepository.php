<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Account\Account;
use App\Domain\Account\AccountNotFound;
use App\Domain\Account\AccountRepository;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\NewAccount;
use App\Domain\Account\Role;
use App\Domain\Shared\UserId;

/**
 * Keeps accounts in memory, numbered from 1 in the order they register.
 */
final class InMemoryAccountRepository implements AccountRepository
{
    /**
     * The accounts, keyed by id.
     *
     * @var array<int, Account>
     */
    private array $accounts = [];

    /**
     * Stores the account as a shopper's under the next id.
     *
     * @throws EmailAlreadyRegistered when another account has the email address.
     */
    public function register(NewAccount $account): UserId
    {
        if ($this->findByEmail($account->credentials->email) instanceof Account) {
            throw EmailAlreadyRegistered::create();
        }

        $id = new UserId(count($this->accounts) + 1);
        $this->accounts[$id->value] = new Account($id, $account->name, $account->credentials, Role::Shopper);

        return $id;
    }

    /**
     * Loads the account with that id.
     *
     * @throws AccountNotFound when there is none.
     */
    public function get(UserId $id): Account
    {
        return $this->accounts[$id->value] ?? throw AccountNotFound::withId($id);
    }

    /**
     * Finds the account of that email address, if any.
     */
    public function findByEmail(EmailAddress $email): ?Account
    {
        foreach ($this->accounts as $account) {
            if ($account->credentials->email->value === $email->value) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Replaces the stored account.
     *
     * @throws AccountNotFound when no account has its id.
     */
    public function save(Account $account): void
    {
        $this->get($account->id);
        $this->accounts[$account->id->value] = $account;
    }
}
