<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * A name was refused because no one could read it or store it.
 */
final class InvalidName extends DomainError
{
    /**
     * How much of a refused name the message quotes.
     */
    private const int QUOTED_LENGTH = 20;

    /**
     * Builds the error for text that is empty or only whitespace.
     */
    public static function blank(): self
    {
        return new self('A name cannot be blank.');
    }

    /**
     * Builds the error for a name over the limit, quoting its beginning.
     */
    public static function tooLong(string $name, int $maxLength): self
    {
        return new self(sprintf(
            'A name has at most %d characters; "%s..." has %d.',
            $maxLength,
            mb_substr($name, 0, self::QUOTED_LENGTH),
            mb_strlen($name),
        ));
    }

    /**
     * Builds the error for bytes that are not valid UTF-8.
     */
    public static function malformed(): self
    {
        return new self('A name must be valid UTF-8 text.');
    }
}
