<?php

declare(strict_types=1);

namespace App\Application\ShoppingList;

use App\Domain\Catalog\ProductId;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ProductNotInList;
use App\Domain\ShoppingList\ShoppingListRepository;

/**
 * Takes a product off a user's own list (use case).
 */
final readonly class RemoveProductFromList
{
    /**
     * Takes the shopping lists.
     */
    public function __construct(private ShoppingListRepository $lists) {}

    /**
     * Removes the product and stores the list.
     *
     * @throws ProductNotInList when the product is not on the owner's list.
     */
    public function handle(UserId $owner, ProductId $productId): void
    {
        $list = $this->lists->forOwner($owner);
        $list->remove($productId);
        $this->lists->save($list);
    }
}
