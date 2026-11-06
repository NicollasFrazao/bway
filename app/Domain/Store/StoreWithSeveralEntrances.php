<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * A store was assembled with more than one entrance sector, so a visit would not know where to start.
 */
final class StoreWithSeveralEntrances extends DomainError
{
    /**
     * Builds the error naming every sector marked as the entrance.
     */
    public static function forStore(StoreId $storeId, SectorId ...$entrances): self
    {
        return new self(sprintf(
            'Store %d has %d entrance sectors (%s); exactly one is required.',
            $storeId->value,
            count($entrances),
            implode(', ', array_map(static fn (SectorId $id): int => $id->value, $entrances)),
        ));
    }
}
