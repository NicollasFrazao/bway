<?php

declare(strict_types=1);

namespace App\Application\Routing;

use App\Domain\Store\Sector;

/**
 * How far the routes of a store are found: the pairs to find, those found, and those without a route.
 */
final readonly class RouteGenerationView
{
    /**
     * Describes the progress.
     *
     * @param  int  $total  Every ordered pair of distinct sectors.
     * @param  int  $found  The pairs with a route kept.
     * @param  list<array{Sector, Sector}>  $unreachable  The pairs no walkway joins, from and to.
     */
    public function __construct(
        public int $total,
        public int $found,
        public array $unreachable,
    ) {}

    /**
     * Tells whether every pair has been searched.
     */
    public function isComplete(): bool
    {
        return $this->found + count($this->unreachable) >= $this->total;
    }
}
