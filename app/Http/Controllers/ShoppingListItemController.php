<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\ShoppingList\AddProductToList;
use App\Application\ShoppingList\RemoveProductFromList;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\ShoppingList\DuplicateListItem;
use App\Domain\ShoppingList\ProductNotInList;
use App\Http\Requests\ShoppingList\AddProductRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Adding a product to the signed-in user's own list and taking one off.
 */
final readonly class ShoppingListItemController
{
    /**
     * Takes the use cases that change the list.
     */
    public function __construct(
        private AddProductToList $add,
        private RemoveProductFromList $remove,
    ) {}

    /**
     * Adds the chosen product.
     *
     * @throws AuthenticationException when nobody is signed in.
     * @throws ValidationException when the product does not exist or is on the list already.
     */
    public function store(AddProductRequest $request): RedirectResponse
    {
        try {
            $this->add->handle(SignedInUser::id($request), $request->productId());
        } catch (ProductNotFound) {
            throw ValidationException::withMessages(['product_id' => __('shopping-list.errors.unknown')]);
        } catch (DuplicateListItem) {
            throw ValidationException::withMessages(['product_id' => __('shopping-list.errors.duplicate')]);
        }

        return back()->with('status', __('shopping-list.status.added'));
    }

    /**
     * Takes the product off the list.
     *
     * @throws AuthenticationException when nobody is signed in.
     * @throws NotFoundHttpException when the product is not on the list.
     */
    public function destroy(Request $request, int $product): RedirectResponse
    {
        try {
            $this->remove->handle(SignedInUser::id($request), new ProductId($product));
        } catch (ProductNotInList $error) {
            throw new NotFoundHttpException($error->getMessage(), $error);
        }

        return redirect()->route('shopping-list')->with('status', __('shopping-list.status.removed'));
    }
}
