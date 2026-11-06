<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * A store's grid was requested before its floor plan was mapped.
 */
final class GridNotMapped extends DomainError
{
    /**
     * Builds the error for the store.
     */
    public static function forStore(StoreId $storeId): self
    {
        return new self(sprintf('The floor plan of store %d has not been mapped yet.', $storeId->value));
    }
}
