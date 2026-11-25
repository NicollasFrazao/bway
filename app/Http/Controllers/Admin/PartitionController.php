<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Store\EditPartitions;
use App\Domain\Grid\InvalidGridArea;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\PartitionId;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Http\Requests\Admin\AreaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Adding and removing the partitions (shelves, dividers) of a store.
 */
final readonly class PartitionController
{
    /**
     * Takes the partitions use case.
     */
    public function __construct(private EditPartitions $partitions) {}

    /**
     * Adds a partition.
     *
     * @throws ValidationException when the rectangle is refused.
     * @throws NotFoundHttpException when no store has the id.
     */
    public function store(AreaRequest $request, int $store): RedirectResponse
    {
        try {
            $this->partitions->add(new StoreId($store), $request->area());
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (InvalidGridArea|LayoutRefused $error) {
            throw LayoutErrors::toValidation($error);
        }

        return redirect()->route('admin.stores.show', $store)->with('status', __('stores.status.partition_added'));
    }

    /**
     * Removes a partition.
     *
     * @throws ValidationException never in practice: removing a partition cannot break a layout.
     * @throws NotFoundHttpException when no store has the id.
     */
    public function destroy(int $store, int $partition): RedirectResponse
    {
        try {
            $this->partitions->remove(new StoreId($store), new PartitionId($partition));
        } catch (StoreNotFound $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        } catch (LayoutRefused $error) {
            throw LayoutErrors::toValidation($error);
        }

        return redirect()->route('admin.stores.show', $store)->with('status', __('stores.status.partition_removed'));
    }
}
