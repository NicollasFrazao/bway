<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

use App\Domain\Shared\DomainError;

/**
 * A file given as a floor plan is not an image that can be decoded.
 */
final class UnreadableFloorPlan extends DomainError
{
    /**
     * Builds the error for a file of the given size.
     */
    public static function ofBytes(int $bytes): self
    {
        return new self(sprintf('The floor plan (%d bytes) is not an image that can be decoded.', $bytes));
    }
}
