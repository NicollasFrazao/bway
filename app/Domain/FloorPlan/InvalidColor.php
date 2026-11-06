<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

use App\Domain\Shared\DomainError;

/**
 * A color was given a channel outside 0 to 255.
 */
final class InvalidColor extends DomainError
{
    /**
     * Builds the error for the refused channels.
     */
    public static function withChannels(int $red, int $green, int $blue): self
    {
        return new self(sprintf('Color channels run from 0 to 255; got (%d, %d, %d).', $red, $green, $blue));
    }
}
