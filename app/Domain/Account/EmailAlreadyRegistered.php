<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\DomainError;

/**
 * A sign-up used an email address another account already has.
 */
final class EmailAlreadyRegistered extends DomainError
{
    /**
     * The error for the address; the message leaves the address out of the log.
     */
    public static function create(): self
    {
        return new self('An account with this email address exists already.');
    }
}
