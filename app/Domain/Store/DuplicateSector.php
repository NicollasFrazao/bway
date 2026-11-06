<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * A store was assembled with two sectors that share an id.
 */
final class DuplicateSector extends DomainError
{
    /**
     * Builds the error for the repeated sector.
     */
    public static function inStore(StoreId $storeId, SectorId $sectorId): self
    {
        return new self(sprintf('Store %d lists sector %d more than once.', $storeId->value, $sectorId->value));
    }
}
