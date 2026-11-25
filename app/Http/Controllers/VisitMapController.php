<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Visit\MapVisit;
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
 * The map of the signed-in shopper's visit: the store's layout with the route drawn, one walk
 * highlighted at a time; it replaces v1's static map image.
 */
final readonly class VisitMapController
{
    /**
     * Takes the visit mapping.
     */
    public function __construct(private MapVisit $map) {}

    /**
     * The map with one walk highlighted (`?leg=N`, from 1; the first by default).
     *
     * @throws AuthenticationException when nobody is signed in.
     * @throws NotFoundHttpException when no store has the id.
     */
    public function show(Request $request, int $store): View
    {
        try {
            $map = $this->map->handle(SignedInUser::id($request), new StoreId($store));
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (GridNotMapped|SectorNotInStore|RouteNotFound) {
            return view('visit.unavailable');
        }

        $leg = min(max($request->integer('leg', 1), 1), max(count($map->legs), 1));

        return view('visit.map', ['storeId' => $store, 'map' => $map, 'current' => $leg]);
    }
}
