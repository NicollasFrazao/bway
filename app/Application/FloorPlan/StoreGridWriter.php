<?php

declare(strict_types=1);

namespace App\Application\FloorPlan;

use App\Domain\Grid\Grid;
use App\Domain\Routing\RouteCache;
use App\Domain\Shared\Transaction;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;

/**
 * Keeps a store's new grid and forgets the routes found on the old one, in one transaction, so a
 * route of the old grid is never served with the new (TD-13).
 */
final readonly class StoreGridWriter
{
    /**
     * Takes where grids and routes are kept, and the transaction.
     */
    public function __construct(
        private StoreGridRepository $grids,
        private RouteCache $routes,
        private Transaction $transaction,
    ) {}

    /**
     * Replaces the store's grid and forgets its routes.
     */
    public function replace(StoreId $storeId, Grid $grid): void
    {
        $this->transaction->run(function () use ($storeId, $grid): void {
            $this->grids->save($storeId, $grid);
            $this->routes->forgetStore($storeId);
        });
    }
}
