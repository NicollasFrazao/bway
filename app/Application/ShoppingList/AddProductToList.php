<?php

declare(strict_types=1);

namespace App\Application\ShoppingList;

use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\DuplicateListItem;
use App\Domain\ShoppingList\ShoppingListRepository;

/**
 * Puts a product of the catalogue on a user's own list (use case).
 */
final readonly class AddProductToList
{
    /**
     * Takes the shopping lists and the catalogue.
     */
    public function __construct(
        private ShoppingListRepository $lists,
        private ProductCatalog $catalog,
    ) {}

    /**
     * Adds the product and stores the list.
     *
     * @throws ProductNotFound when the catalogue has no product with that id.
     * @throws DuplicateListItem when the product is on the list already.
     */
    public function handle(UserId $owner, ProductId $productId): void
    {
        $list = $this->lists->forOwner($owner);
        $list->add($this->catalog->get($productId));
        $this->lists->save($list);
    }
}
