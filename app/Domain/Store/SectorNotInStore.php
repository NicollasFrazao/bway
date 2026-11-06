<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * A sector was requested from a store that does not have it.
 */
final class SectorNotInStore extends DomainError
{
    /**
     * Builds the error for the missing sector.
     */
    public static function forSector(StoreId $storeId, SectorId $sectorId): self
    {
        return new self(sprintf('Store %d has no sector %d.', $storeId->value, $sectorId->value));
    }
}
