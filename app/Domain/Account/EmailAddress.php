<?php

declare(strict_types=1);

namespace App\Domain\Account;

/**
 * An email address, trimmed and lower-cased, so the same mailbox typed twice is one address.
 */
final readonly class EmailAddress
{
    /**
     * The longest address a mail server accepts (RFC 5321 path limit minus the brackets).
     */
    private const int MAX_LENGTH = 254;

    /**
     * Holds an already normalised address; use {@see EmailAddress::from()} to build one.
     */
    private function __construct(public string $value) {}

    /**
     * Reads an address as a person typed it.
     *
     * @throws InvalidAccountData when it is not an email address or is longer than 254 characters.
     */
    public static function from(string $text): self
    {
        $address = mb_strtolower(trim($text));

        if (
            mb_strlen($address) > self::MAX_LENGTH
            || filter_var($address, FILTER_VALIDATE_EMAIL) === false
        ) {
            throw InvalidAccountData::of(AccountField::Email, 'not an email address');
        }

        return new self($address);
    }
}
