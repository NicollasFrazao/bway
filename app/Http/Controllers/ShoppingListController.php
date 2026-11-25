<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\ShoppingList\ClearShoppingList;
use App\Application\ShoppingList\SearchProducts;
use App\Application\ShoppingList\ShowShoppingList;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The signed-in user's own shopping list. No route names a user or a list: the list is always the
 * session's, so there is no other user's list to reach.
 */
final readonly class ShoppingListController
{
    /**
     * Takes the use cases of the page.
     */
    public function __construct(
        private ShowShoppingList $show,
        private SearchProducts $search,
        private ClearShoppingList $clear,
    ) {}

    /**
     * The list, and the products that match the search term, if one was typed.
     *
     * @throws AuthenticationException when nobody is signed in.
     */
    public function show(Request $request): View
    {
        $term = $request->string('search')->toString();

        return view('shopping-list.show', [
            'items' => $this->show->handle(SignedInUser::id($request)),
            'search' => $term,
            'products' => $this->search->handle($term),
        ]);
    }

    /**
     * Empties the list.
     *
     * @throws AuthenticationException when nobody is signed in.
     */
    public function clear(Request $request): RedirectResponse
    {
        $this->clear->handle(SignedInUser::id($request));

        return redirect()->route('shopping-list')->with('status', __('shopping-list.status.cleared'));
    }
}
