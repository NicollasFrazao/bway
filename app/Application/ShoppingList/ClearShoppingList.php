<?php

declare(strict_types=1);

namespace App\Application\ShoppingList;

use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingListRepository;

/**
 * Empties a user's own list (use case).
 */
final readonly class ClearShoppingList
{
    /**
     * Takes the shopping lists.
     */
    public function __construct(private ShoppingListRepository $lists) {}

    /**
     * Removes every item and stores the empty list.
     */
    public function handle(UserId $owner): void
    {
        $list = $this->lists->forOwner($owner);
        $list->clear();
        $this->lists->save($list);
    }
}
