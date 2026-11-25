<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Visit\PlanVisit;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The signed-in shopper's visit to a store: the sectors in walking order, with what to pick up in each
 * and what the store does not sell.
 */
final readonly class VisitController
{
    /**
     * Takes the visit planning.
     */
    public function __construct(private PlanVisit $plan) {}

    /**
     * The visit, or why the store cannot be visited yet.
     *
     * @throws AuthenticationException when nobody is signed in.
     * @throws NotFoundHttpException when no store has the id.
     */
    public function show(Request $request, int $store): View
    {
        try {
            return view('visit.show', [
                'storeId' => $store,
                'plan' => $this->plan->handle(SignedInUser::id($request), new StoreId($store)),
            ]);
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (GridNotMapped|SectorNotInStore|RouteNotFound) {
            return view('visit.unavailable');
        }
    }
}
