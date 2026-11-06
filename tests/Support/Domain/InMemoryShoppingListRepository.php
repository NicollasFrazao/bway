<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\ShoppingList\ShoppingListRepository;

/**
 * Keeps shopping lists in memory, as copies, so a change is stored only when it is saved.
 */
final class InMemoryShoppingListRepository implements ShoppingListRepository
{
    /**
     * The saved lists, keyed by owner id.
     *
     * @var array<int, ShoppingList>
     */
    private array $lists = [];

    /**
     * Returns a copy of the saved list, or an empty one.
     */
    public function forOwner(UserId $owner): ShoppingList
    {
        $saved = $this->lists[$owner->value] ?? null;

        if ($saved === null) {
            return ShoppingList::empty($owner);
        }

        return $this->copy($saved);
    }

    /**
     * Stores a copy of the list, replacing the previous one of its owner.
     */
    public function save(ShoppingList $list): void
    {
        $this->lists[$list->owner->value] = $this->copy($list);
    }

    /**
     * A list with the same owner and items that shares no state with the original.
     */
    private function copy(ShoppingList $list): ShoppingList
    {
        return ShoppingList::restore($list->owner, ...$list->items());
    }
}
