<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\Name;

/**
 * An account about to be registered: it has no id yet, and it is a shopper's.
 */
final readonly class NewAccount
{
    /**
     * Describes the account a sign-up asks for.
     */
    public function __construct(
        public Name $name,
        public Credentials $credentials,
    ) {}
}
