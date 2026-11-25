<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\DomainError;
use App\Domain\Shared\UserId;

/**
 * No account has the id asked for.
 */
final class AccountNotFound extends DomainError
{
    /**
     * The error for the id.
     */
    public static function withId(UserId $id): self
    {
        return new self("No account has the id {$id->value}.");
    }
}
