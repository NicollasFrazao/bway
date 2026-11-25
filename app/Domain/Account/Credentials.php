<?php

declare(strict_types=1);

namespace App\Domain\Account;

/**
 * What an account signs in with: its email address and the hash of its password.
 */
final readonly class Credentials
{
    /**
     * Pairs the address with the hash.
     */
    public function __construct(
        public EmailAddress $email,
        public HashedPassword $password,
    ) {}

    /**
     * The same address with a new password.
     */
    public function withPassword(HashedPassword $password): self
    {
        return new self($this->email, $password);
    }
}
