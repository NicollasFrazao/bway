<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * An identifier was built from a value that cannot be a stored key.
 */
final class InvalidIdentifier extends DomainError
{
    /**
     * Builds the error for a zero or negative value.
     *
     * @param  class-string<Identifier>  $kind  The identifier class that refused the value.
     */
    public static function notPositive(string $kind, int $value): self
    {
        return new self(sprintf('%s must be a positive integer, %d given.', $kind, $value));
    }
}
