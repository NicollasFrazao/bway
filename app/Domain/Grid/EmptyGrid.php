<?php

declare(strict_types=1);

namespace App\Domain\Grid;

use App\Domain\Shared\DomainError;

/**
 * A grid was built without a single cell.
 */
final class EmptyGrid extends DomainError
{
    /**
     * Builds the error.
     */
    public static function withoutCells(): self
    {
        return new self('A grid has at least one row and one column.');
    }
}
