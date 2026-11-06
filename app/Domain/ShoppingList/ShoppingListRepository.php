<?php

declare(strict_types=1);

namespace App\Domain\ShoppingList;

use App\Domain\Shared\UserId;

/**
 * Where shopping lists are kept between requests (port; implemented in Infrastructure).
 */
interface ShoppingListRepository
{
    /**
     * Loads the list of a user; a user who has never added anything gets an empty list.
     */
    public function forOwner(UserId $owner): ShoppingList;

    /**
     * Stores the list as it is now, replacing whatever was stored for its owner.
     */
    public function save(ShoppingList $list): void;
}
