<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Store\EditAssortment;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Http\Requests\Admin\AssortmentRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Which sector of a store sells each product.
 */
final readonly class AssortmentController
{
    /**
     * Takes the assortment use case.
     */
    public function __construct(private EditAssortment $assortment) {}

    /**
     * The form: every product with a choice of the store's sectors.
     *
     * @throws NotFoundHttpException when no store has the id.
     */
    public function edit(int $store): View
    {
        try {
            return view('admin.stores.assortment', ['view' => $this->assortment->show(new StoreId($store))]);
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        }
    }

    /**
     * Saves the assortment.
     *
     * @throws ValidationException when a sector or a product does not exist.
     * @throws NotFoundHttpException when no store has the id.
     */
    public function update(AssortmentRequest $request, int $store): RedirectResponse
    {
        try {
            $this->assortment->save(new StoreId($store), $request->placements());
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (SectorNotInStore|ProductNotFound) {
            throw ValidationException::withMessages(['sectors' => __('stores.errors.assortment')]);
        }

        return redirect()->route('admin.stores.show', $store)->with('status', __('stores.status.assortment'));
    }
}
