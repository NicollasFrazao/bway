<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * A store was assembled with two partitions that share an id.
 */
final class DuplicatePartition extends DomainError
{
    /**
     * Builds the error for the repeated partition.
     */
    public static function inStore(StoreId $storeId, PartitionId $partitionId): self
    {
        return new self(sprintf('Store %d lists partition %d more than once.', $storeId->value, $partitionId->value));
    }
}
