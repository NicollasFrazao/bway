<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Store\EditSectors;
use App\Domain\Grid\InvalidGridArea;
use App\Domain\Shared\InvalidName;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Http\Requests\Admin\AreaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Adding, changing and removing the sectors of a store.
 */
final readonly class SectorController
{
    /**
     * Takes the sectors use case.
     */
    public function __construct(private EditSectors $sectors) {}

    /**
     * Adds a sales sector.
     *
     * @throws ValidationException when the name or the rectangle is refused.
     * @throws NotFoundHttpException when no store has the id.
     */
    public function store(AreaRequest $request, int $store): RedirectResponse
    {
        try {
            $this->sectors->add(new StoreId($store), $request->string('name')->toString(), $request->area());
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (InvalidName|InvalidGridArea|LayoutRefused $error) {
            throw LayoutErrors::toValidation($error);
        }

        return $this->back($store, 'sector_added');
    }

    /**
     * Renames or moves a sector.
     *
     * @throws ValidationException when the name or the rectangle is refused.
     * @throws NotFoundHttpException when the store or the sector does not exist.
     */
    public function update(AreaRequest $request, int $store, int $sector): RedirectResponse
    {
        try {
            $this->sectors->update(new StoreId($store), new SectorId($sector), $request->string('name')->toString(), $request->area());
        } catch (StoreNotFound|SectorNotInStore $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (InvalidName|InvalidGridArea|LayoutRefused $error) {
            throw LayoutErrors::toValidation($error);
        }

        return $this->back($store, 'sector_saved');
    }

    /**
     * Removes a sales sector.
     *
     * @throws ValidationException when the sector is the entrance.
     * @throws NotFoundHttpException when the store or the sector does not exist.
     */
    public function destroy(int $store, int $sector): RedirectResponse
    {
        try {
            $this->sectors->remove(new StoreId($store), new SectorId($sector));
        } catch (StoreNotFound|SectorNotInStore $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (LayoutRefused $error) {
            throw LayoutErrors::toValidation($error);
        }

        return $this->back($store, 'sector_removed');
    }

    /**
     * Back to the store's layout page with a status line.
     */
    private function back(int $store, string $status): RedirectResponse
    {
        return redirect()->route('admin.stores.show', $store)->with('status', __("stores.status.{$status}"));
    }
}
