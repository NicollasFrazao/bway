<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Store\CreateStore;
use App\Domain\FloorPlan\UnreadableFloorPlan;
use App\Domain\FloorPlan\WallFrameNotFound;
use App\Domain\Grid\InvalidGridArea;
use App\Domain\Shared\InvalidName;
use App\Domain\Store\LayoutRefused;
use App\Http\Requests\Admin\CreateStoreRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

/**
 * A new store from its floor plan; v1 could not create one at all.
 */
final readonly class NewStoreController
{
    /**
     * Takes the use case that creates a store.
     */
    public function __construct(private CreateStore $create) {}

    /**
     * The new-store form.
     */
    public function create(): View
    {
        return view('admin.stores.create');
    }

    /**
     * Creates the store from its plan and opens its layout page.
     *
     * @throws ValidationException when the name, the entrance or the plan is refused.
     */
    public function store(CreateStoreRequest $request): RedirectResponse
    {
        try {
            $id = $this->create->handle($request->string('name')->toString(), $request->entrance(), $request->floorPlan());
        } catch (InvalidName|InvalidGridArea|LayoutRefused|UnreadableFloorPlan|WallFrameNotFound $error) {
            throw LayoutErrors::toValidation($error);
        }

        return redirect()->route('admin.stores.show', $id->value)->with('status', __('stores.status.created'));
    }
}
