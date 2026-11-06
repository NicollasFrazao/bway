<?php

declare(strict_types=1);

namespace App\Domain\Routing;

use App\Domain\Grid\GridCoordinate;
use App\Domain\Shared\DomainError;

/**
 * No walkway connects the start to the target. v1 answered an empty route, which its visit order
 * counted as length 0; v2 never treats a missing route as a short one.
 */
final class RouteNotFound extends DomainError
{
    /**
     * Builds the error for the two cells.
     */
    public static function between(GridCoordinate $start, GridCoordinate $target): self
    {
        return new self(sprintf(
            'No walkway leads from (%d, %d) to (%d, %d).',
            $start->x,
            $start->y,
            $target->x,
            $target->y,
        ));
    }
}
