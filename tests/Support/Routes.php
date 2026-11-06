<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\Route;

/**
 * Builds routes from [x, y] pairs, which read better in a test than coordinate objects.
 */
final class Routes
{
    /**
     * A route through the given cells, from the first to the last.
     *
     * @param  array{int, int}  ...$cells  [x, y] pairs, each one step from the one before.
     */
    public static function through(array ...$cells): Route
    {
        return new Route(array_map(static fn (array $cell): GridCoordinate => new GridCoordinate($cell[0], $cell[1]), array_values($cells)));
    }
}
