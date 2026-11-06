<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Shared\DomainError;

/**
 * The catalogue has no product with the requested identity.
 */
final class ProductNotFound extends DomainError
{
    /**
     * Builds the error for the missing id.
     */
    public static function withId(ProductId $id): self
    {
        return new self(sprintf('The catalogue has no product %d.', $id->value));
    }
}
