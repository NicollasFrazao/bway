<?php

declare(strict_types=1);

namespace App\Domain\Grid;

use App\Domain\Shared\DomainError;

/**
 * A grid coordinate was given a negative x or y; the grid starts at (0, 0).
 */
final class NegativeCoordinate extends DomainError
{
    /**
     * Builds the error for the refused pair.
     */
    public static function at(int $x, int $y): self
    {
        return new self(sprintf('Grid coordinates start at (0, 0); (%d, %d) is outside the grid.', $x, $y));
    }
}
