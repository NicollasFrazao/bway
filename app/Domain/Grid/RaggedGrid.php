<?php

declare(strict_types=1);

namespace App\Domain\Grid;

use App\Domain\Shared\DomainError;

/**
 * A grid was built with rows of different lengths, so it is not a rectangle.
 */
final class RaggedGrid extends DomainError
{
    /**
     * Builds the error for the first row whose length differs from the first row's.
     */
    public static function atRow(int $row, int $length, int $expected): self
    {
        return new self(sprintf('Every row of a grid has %d cells; row %d has %d.', $expected, $row, $length));
    }
}
