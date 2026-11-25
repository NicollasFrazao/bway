<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Routing\RouteCache;
use App\Domain\Shared\Transaction;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\Partition;
use App\Domain\Store\Sector;
use App\Domain\Store\Store;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;

/**
 * Writes a changed layout of a store: checked against the store's grid, then saved with the store's
 * routes forgotten, in one transaction, since they were found on the old layout.
 */
final readonly class LayoutWriter
{
    /**
     * Takes the stores, their grids, the route cache and the transaction.
     */
    public function __construct(
        private StoreRepository $stores,
        private StoreGridRepository $grids,
        private RouteCache $routes,
        private Transaction $transaction,
    ) {}

    /**
     * Saves the store with these sectors and partitions, once the Store aggregate accepts them.
     *
     * @param  list<Sector>  $sectors  Every sector the store will have.
     * @param  list<Partition>  $partitions  Every partition the store will have.
     *
     * @throws LayoutRefused when the parts overlap, leave the grid, or leave the store without one
     *                       entrance.
     * @throws StoreNotFound when the store no longer exists.
     */
    public function write(Store $store, array $sectors, array $partitions): void
    {
        $changed = new Store($store->id, $store->name, $sectors, $partitions);

        try {
            $changed->ensureFitsInside($this->grids->forStore($changed->id));
        } catch (GridNotMapped) {
            // A store without a grid has no bounds to check yet; mapping its plan checks them.
        }

        $this->transaction->run(function () use ($changed): void {
            $this->stores->save($changed);
            $this->routes->forgetStore($changed->id);
        });
    }
}
