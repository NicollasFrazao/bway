<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\DomainError;

/**
 * A sign-in failed. The same error for an unknown email and a wrong password, so the answer does not
 * tell which emails have an account.
 */
final class InvalidCredentials extends DomainError
{
    /**
     * The error, without saying which of the two was wrong.
     */
    public static function create(): self
    {
        return new self('The email or the password is wrong.');
    }
}
