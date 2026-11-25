<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Application\FloorPlan\StoreGridWriter;
use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\UnreadableFloorPlan;
use App\Domain\FloorPlan\WallFrameNotFound;
use App\Domain\Grid\InvalidGridArea;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Domain\Shared\Transaction;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreRepository;

/**
 * Creates a store from its name, its floor plan and where its entrance is (use case). The plan is
 * mapped before anything is written, and the store and its grid are written together; v1 had no way
 * to create a store at all.
 */
final readonly class CreateStore
{
    /**
     * Takes the stores, the mapper, the writer of grids and the transaction.
     */
    public function __construct(
        private StoreRepository $stores,
        private FloorPlanMapper $mapper,
        private StoreGridWriter $grids,
        private Transaction $transaction,
    ) {}

    /**
     * Maps the plan, then writes the store with its entrance and its grid.
     *
     * @param  string  $floorPlan  The image file as uploaded.
     *
     * @throws InvalidName when the name is blank or too long.
     * @throws InvalidGridArea when the entrance starts at a negative cell or has no width or height.
     * @throws UnreadableFloorPlan when the file is not an image that can be decoded.
     * @throws WallFrameNotFound when the plan has no wall to frame the store.
     * @throws LayoutRefused when the entrance reaches beyond the mapped grid; nothing is written.
     */
    public function handle(string $name, AreaInput $entrance, string $floorPlan): StoreId
    {
        $storeName = Name::from($name);
        $area = $entrance->area();
        $grid = $this->mapper->mapFile($floorPlan);

        return $this->transaction->run(function () use ($storeName, $area, $grid): StoreId {
            $store = $this->stores->add($storeName, $area);
            $store->ensureFitsInside($grid);
            $this->grids->replace($store->id, $grid);

            return $store->id;
        });
    }
}
