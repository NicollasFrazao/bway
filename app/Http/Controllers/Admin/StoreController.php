<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Store\ListStores;
use App\Application\Store\ShowStoreLayout;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Store management, for administrators only (the routes require `can:manage-stores`): the stores,
 * and one store's layout page.
 */
final readonly class StoreController
{
    /**
     * Takes the use cases of the pages.
     */
    public function __construct(
        private ListStores $stores,
        private ShowStoreLayout $layout,
    ) {}

    /**
     * Every store.
     */
    public function index(): View
    {
        return view('admin.stores.index', ['stores' => $this->stores->handle()]);
    }

    /**
     * The layout page: the mapped grid with the sectors and partitions over it, their forms, and the
     * progress of the routes.
     *
     * @throws NotFoundHttpException when no store has the id.
     */
    public function show(int $store): View
    {
        try {
            return view('admin.stores.show', ['layout' => $this->layout->handle(new StoreId($store))]);
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        }
    }
}
