<?php

declare(strict_types=1);

namespace App\Application\FloorPlan;

use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\UnreadableFloorPlan;
use App\Domain\FloorPlan\WallFrameNotFound;
use App\Domain\Grid\Grid;
use App\Domain\Routing\RouteCache;
use App\Domain\Store\LayoutOutsideGrid;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * Maps a store's floor plan to its grid and keeps it, in place of any earlier mapping (use case).
 *
 * The routes found on the earlier grid are forgotten, since walkways may have moved.
 */
final readonly class MapFloorPlan
{
    /**
     * Takes the store to map, the mapper, and where grids and routes are kept.
     */
    public function __construct(
        private StoreRepository $stores,
        private FloorPlanMapper $mapper,
        private StoreGridRepository $grids,
        private RouteCache $routes,
    ) {}

    /**
     * Maps the plan, checks the store's layout fits it, and keeps the grid under the store's id.
     *
     * @param  string  $floorPlan  The image file, as read from storage or an upload.
     *
     * @throws StoreNotFound when no store has that id.
     * @throws UnreadableFloorPlan when the file is not an image that can be decoded.
     * @throws WallFrameNotFound when the plan has no wall to frame the store.
     * @throws LayoutOutsideGrid when a sector or partition of the store reaches beyond the grid.
     */
    public function handle(StoreId $storeId, string $floorPlan): Grid
    {
        $store = $this->stores->get($storeId);
        $grid = $this->mapper->mapFile($floorPlan);
        $store->ensureFitsInside($grid);

        $this->grids->save($storeId, $grid);
        $this->routes->forgetStore($storeId);

        return $grid;
    }
}
