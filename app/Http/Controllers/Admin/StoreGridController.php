<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Store\ShowStoreGrid;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\StoreId;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The picture of a store's mapped grid, which the layout page draws the sectors over.
 */
final readonly class StoreGridController
{
    /**
     * Takes the picture use case.
     */
    public function __construct(private ShowStoreGrid $grid) {}

    /**
     * The mapped grid as a PNG image, one pixel per cell.
     *
     * @throws NotFoundHttpException when the store has no grid.
     */
    public function show(int $store): Response
    {
        try {
            $png = $this->grid->handle(new StoreId($store));
        } catch (GridNotMapped $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        }

        return new Response($png, Response::HTTP_OK, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, no-cache']);
    }
}
