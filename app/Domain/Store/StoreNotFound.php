<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * No store has the requested identity.
 */
final class StoreNotFound extends DomainError
{
    /**
     * Builds the error for the missing id.
     */
    public static function withId(StoreId $id): self
    {
        return new self(sprintf('No store has id %d.', $id->value));
    }
}
