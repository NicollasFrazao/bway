<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\FloorPlan\MapFloorPlan;
use App\Domain\FloorPlan\UnreadableFloorPlan;
use App\Domain\FloorPlan\WallFrameNotFound;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Http\Requests\Admin\FloorPlanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A new floor plan for a store, mapped in place of the old one.
 */
final readonly class FloorPlanController
{
    /**
     * Takes the mapping use case.
     */
    public function __construct(private MapFloorPlan $map) {}

    /**
     * Maps the uploaded plan in place of the old one; the store's routes are forgotten.
     *
     * @throws ValidationException when the plan cannot be read, has no wall frame, or is too small for
     *                             the layout.
     * @throws NotFoundHttpException when no store has the id.
     */
    public function update(FloorPlanRequest $request, int $store): RedirectResponse
    {
        try {
            $this->map->handle(new StoreId($store), $request->floorPlan());
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (UnreadableFloorPlan|WallFrameNotFound $error) {
            throw LayoutErrors::toValidation($error);
        } catch (LayoutRefused) {
            throw ValidationException::withMessages(['floor_plan' => __('stores.errors.too_small')]);
        }

        return redirect()->route('admin.stores.show', $store)->with('status', __('stores.status.mapped'));
    }
}
