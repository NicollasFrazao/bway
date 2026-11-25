<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Application\Routing\RouteGenerationView;
use App\Application\Visit\GridSize;
use App\Domain\Store\Store;

/**
 * A store as its management page shows it: the layout, the size of its grid (null until mapped) and
 * how far its routes are found.
 */
final readonly class StoreLayout
{
    /**
     * Describes the page.
     */
    public function __construct(
        public Store $store,
        public ?GridSize $size,
        public RouteGenerationView $routes,
    ) {}
}
