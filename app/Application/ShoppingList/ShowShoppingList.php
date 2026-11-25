<?php

declare(strict_types=1);

namespace App\Application\ShoppingList;

use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ShoppingListRepository;

/**
 * The items of a user's own list, by product name as v1 showed them (use case).
 */
final readonly class ShowShoppingList
{
    /**
     * Takes the shopping lists.
     */
    public function __construct(private ShoppingListRepository $lists) {}

    /**
     * The owner's items, ordered by product name, ignoring case.
     *
     * @return list<ListItem>
     */
    public function handle(UserId $owner): array
    {
        $items = $this->lists->forOwner($owner)->items();
        usort(
            $items,
            static fn (ListItem $left, ListItem $right): int => strnatcasecmp(
                $left->productName->value,
                $right->productName->value,
            ),
        );

        return $items;
    }
}
