<?php

declare(strict_types=1);

namespace App\Application\ShoppingList;

use App\Domain\Catalog\ProductId;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ProductNotInList;
use App\Domain\ShoppingList\ShoppingListRepository;

/**
 * Marks a product of a user's own list as bought during a visit: it leaves the list, so the next
 * plan no longer stops for it (use case).
 */
final readonly class AcquireItem
{
    /**
     * Takes the shopping lists.
     */
    public function __construct(private ShoppingListRepository $lists) {}

    /**
     * Takes the item off the list and stores the list.
     *
     * @throws ProductNotInList when the product is not on the owner's list.
     */
    public function handle(UserId $owner, ProductId $productId): ListItem
    {
        $list = $this->lists->forOwner($owner);
        $item = $list->acquire($productId);
        $this->lists->save($list);

        return $item;
    }
}
