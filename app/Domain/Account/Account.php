<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\Name;
use App\Domain\Shared\UserId;

/**
 * A person who signs in to bway: who they are, how they sign in and what they may do.
 *
 * The id is the {@see UserId} that owns a shopping list, so the list of an account is found from the
 * account alone.
 */
final readonly class Account
{
    /**
     * Describes a registered account.
     */
    public function __construct(
        public UserId $id,
        public Name $name,
        public Credentials $credentials,
        public Role $role,
    ) {}

    /**
     * The same account with a new password.
     */
    public function withPassword(HashedPassword $password): self
    {
        return new self($this->id, $this->name, $this->credentials->withPassword($password), $this->role);
    }

    /**
     * Tells whether the account may create and change stores.
     */
    public function canManageStores(): bool
    {
        return $this->role === Role::Administrator;
    }
}
