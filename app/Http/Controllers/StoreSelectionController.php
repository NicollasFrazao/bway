<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Store\ListStores;
use Illuminate\Contracts\View\View;

/**
 * The stores a shopper can plan a visit to; v1 had store 1 written into its screens.
 */
final readonly class StoreSelectionController
{
    /**
     * Takes the use case that lists the stores.
     */
    public function __construct(private ListStores $stores) {}

    /**
     * Every store, to choose one.
     */
    public function index(): View
    {
        return view('stores.index', ['stores' => $this->stores->handle()]);
    }
}
