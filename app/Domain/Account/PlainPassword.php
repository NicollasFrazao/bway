<?php

declare(strict_types=1);

namespace App\Domain\Account;

use SensitiveParameter;

/**
 * A new password as a person typed it, before hashing: at least 8 characters and at most 72 bytes,
 * the part bcrypt reads (a longer one would be cut without notice).
 */
final readonly class PlainPassword
{
    /**
     * The fewest characters a password may have.
     */
    private const int MIN_LENGTH = 8;

    /**
     * The bytes bcrypt reads; anything after them is ignored by the hash.
     */
    private const int MAX_BYTES = 72;

    /**
     * Holds an accepted password; use {@see PlainPassword::from()} to build one.
     */
    private function __construct(#[SensitiveParameter] public string $value) {}

    /**
     * Accepts a new password.
     *
     * @throws InvalidAccountData when it is shorter than 8 characters or longer than 72 bytes.
     */
    public static function from(#[SensitiveParameter] string $text): self
    {
        if (
            mb_strlen($text) < self::MIN_LENGTH
            || strlen($text) > self::MAX_BYTES
        ) {
            throw InvalidAccountData::of(AccountField::Password, 'between 8 characters and 72 bytes');
        }

        return new self($text);
    }
}
