<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Application\Routing\TrackRouteGeneration;
use App\Application\Visit\GridSize;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * A store with its layout, its grid's size and the progress of its routes (use case).
 */
final readonly class ShowStoreLayout
{
    /**
     * Takes the stores, their grids and the tracking of their routes.
     */
    public function __construct(
        private StoreRepository $stores,
        private StoreGridRepository $grids,
        private TrackRouteGeneration $routes,
    ) {}

    /**
     * The store's management view.
     *
     * @throws StoreNotFound when no store has the id.
     */
    public function handle(StoreId $storeId): StoreLayout
    {
        $store = $this->stores->get($storeId);

        try {
            $grid = $this->grids->forStore($storeId);
            $size = new GridSize($grid->width, $grid->height);
        } catch (GridNotMapped) {
            $size = null;
        }

        return new StoreLayout($store, $size, $this->routes->progress($storeId));
    }
}
