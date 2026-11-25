<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\DomainError;

/**
 * A value typed for an account (a name, an email, a password) is not valid; the field says which.
 */
final class InvalidAccountData extends DomainError
{
    /**
     * Holds the field and the message; use {@see InvalidAccountData::of()} to build one.
     */
    private function __construct(
        public readonly AccountField $field,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The error for one field, with the reason in words for the log.
     */
    public static function of(AccountField $field, string $reason): self
    {
        return new self($field, "Invalid {$field->value}: {$reason}.");
    }
}
