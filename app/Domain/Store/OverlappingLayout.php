<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * Two parts of a store's layout (sectors or partitions) were placed on a shared cell.
 */
final class OverlappingLayout extends DomainError
{
    /**
     * Builds the error naming both parts, such as "sector 2" and "partition 4".
     */
    public static function between(StoreId $storeId, string $part, string $otherPart): self
    {
        return new self(sprintf('In store %d, %s overlaps %s.', $storeId->value, $part, $otherPart));
    }
}
