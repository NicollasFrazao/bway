<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\ShoppingList\AcquireItem;
use App\Domain\Catalog\ProductId;
use App\Domain\ShoppingList\ProductNotInList;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Ticking an item off during a visit (v1's `ic_adquirido`): it leaves the shopper's own list.
 */
final readonly class AcquiredItemController
{
    /**
     * Takes the use case that acquires an item.
     */
    public function __construct(private AcquireItem $acquire) {}

    /**
     * Marks the product as bought and goes back to the visit, planned again without it.
     *
     * @throws AuthenticationException when nobody is signed in.
     * @throws NotFoundHttpException when the product is not on the list.
     */
    public function store(Request $request, int $store, int $product): RedirectResponse
    {
        try {
            $item = $this->acquire->handle(SignedInUser::id($request), new ProductId($product));
        } catch (ProductNotInList $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        }

        return redirect()->route('visit', $store)->with('status', __('visit.status.acquired', ['product' => $item->productName->value]));
    }
}
