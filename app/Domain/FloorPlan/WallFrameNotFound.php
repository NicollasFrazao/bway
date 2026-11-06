<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

use App\Domain\Shared\DomainError;

/**
 * A floor plan has no wall, so there is no outline of the store to map.
 */
final class WallFrameNotFound extends DomainError
{
    /**
     * Builds the error for a plan of the given size.
     */
    public static function inImage(int $width, int $height): self
    {
        return new self(sprintf(
            'The %d x %d floor plan has no wall pixel; the outline of the store must be drawn as walls.',
            $width,
            $height,
        ));
    }
}
