<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Store\ListStores;
use Illuminate\Contracts\View\View;

/**
 * Store management, for administrators only (the routes require `can:manage-stores`). B5 lists the
 * stores; creating and mapping one is B7 (#10).
 */
final readonly class StoreController
{
    /**
     * Takes the use case that lists the stores.
     */
    public function __construct(private ListStores $stores) {}

    /**
     * Every store with the number of its sectors.
     */
    public function index(): View
    {
        return view('admin.stores.index', ['stores' => $this->stores->handle()]);
    }
}
