<?php

declare(strict_types=1);

namespace App\Domain\ShoppingList;

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Shared\UserId;

/**
 * The products a user intends to buy (aggregate root); each product appears at most once.
 *
 * A user has one list, so the list is identified by its owner. Items keep the order in which they
 * were added; ordering for display is the reader's concern.
 */
final class ShoppingList
{
    /**
     * The items, keyed by product id so a duplicate is found without a scan.
     *
     * @var array<int, ListItem>
     */
    private array $items = [];

    /**
     * Starts an empty list; use {@see ShoppingList::empty()} or {@see ShoppingList::restore()}.
     */
    private function __construct(public readonly UserId $owner) {}

    /**
     * Starts the list of a user who has added nothing yet.
     */
    public static function empty(UserId $owner): self
    {
        return new self($owner);
    }

    /**
     * Rebuilds a list from stored items, checking them as if they were added one by one.
     *
     * @throws DuplicateListItem when two items are for the same product.
     */
    public static function restore(UserId $owner, ListItem ...$items): self
    {
        $list = new self($owner);

        foreach ($items as $item) {
            $list->append($item);
        }

        return $list;
    }

    /**
     * Puts a product of the catalogue on the list.
     *
     * @throws DuplicateListItem when the product is already on the list.
     */
    public function add(Product $product): void
    {
        $this->append(new ListItem($product->id, $product->name));
    }

    /**
     * Takes a product off the list because the user no longer wants it.
     *
     * @throws ProductNotInList when the product is not on the list.
     */
    public function remove(ProductId $productId): void
    {
        $this->takeOut($productId);
    }

    /**
     * Marks a product as bought during a visit: the item leaves the list.
     *
     * @return ListItem The item acquired, as it was on the list.
     *
     * @throws ProductNotInList when the product is not on the list.
     */
    public function acquire(ProductId $productId): ListItem
    {
        return $this->takeOut($productId);
    }

    /**
     * Removes every item.
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Tells whether the product is on the list.
     */
    public function contains(ProductId $productId): bool
    {
        return isset($this->items[$productId->value]);
    }

    /**
     * The items, in the order they were added.
     *
     * @return list<ListItem>
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    /**
     * Adds an item unless its product is already on the list.
     *
     * @throws DuplicateListItem when the product is already on the list.
     */
    private function append(ListItem $item): void
    {
        if ($this->contains($item->productId)) {
            throw DuplicateListItem::forProduct($item->productId);
        }

        $this->items[$item->productId->value] = $item;
    }

    /**
     * Removes the item of a product and returns it.
     *
     * @throws ProductNotInList when the product is not on the list.
     */
    private function takeOut(ProductId $productId): ListItem
    {
        $item = $this->items[$productId->value] ?? throw ProductNotInList::forProduct($productId);
        unset($this->items[$productId->value]);

        return $item;
    }
}
