<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * A store was assembled with no entrance sector, so a visit would have nowhere to start.
 */
final class StoreWithoutEntrance extends DomainError
{
    /**
     * Builds the error for the store missing its entrance.
     */
    public static function forStore(StoreId $storeId): self
    {
        return new self(sprintf('Store %d has no entrance sector; exactly one is required.', $storeId->value));
    }
}
