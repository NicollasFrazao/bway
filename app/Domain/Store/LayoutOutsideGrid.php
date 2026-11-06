<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Grid\Grid;
use App\Domain\Shared\DomainError;

/**
 * A sector or partition of a store reaches beyond the grid its floor plan was mapped to.
 */
final class LayoutOutsideGrid extends DomainError
{
    /**
     * Builds the error naming the part, such as "sector 2", and the size of the grid.
     */
    public static function forPart(StoreId $storeId, string $part, Grid $grid): self
    {
        return new self(sprintf(
            'In store %d, %s reaches beyond the %d x %d grid of its floor plan.',
            $storeId->value,
            $part,
            $grid->width,
            $grid->height,
        ));
    }
}
