<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Routing\TrackRouteGeneration;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Starts the finding of a store's routes on the queue; the layout page shows its progress.
 */
final readonly class RouteGenerationController
{
    /**
     * Takes the tracking use case.
     */
    public function __construct(private TrackRouteGeneration $routes) {}

    /**
     * Queues the generation and goes back to the layout page.
     *
     * @throws NotFoundHttpException when no store has the id.
     */
    public function store(int $store): RedirectResponse
    {
        try {
            $this->routes->start(new StoreId($store));
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        }

        return redirect()->route('admin.stores.show', $store)->with('status', __('stores.status.generating'));
    }
}
