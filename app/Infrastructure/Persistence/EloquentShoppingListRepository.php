<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Infrastructure\Persistence\Eloquent\ShoppingListItemRecord;
use App\Infrastructure\Persistence\Eloquent\ShoppingListRecord;
use Illuminate\Database\ConnectionInterface;

/**
 * Shopping lists kept in `shopping_lists` (one row per owner) and `shopping_list_items` (one row per
 * item, with its position).
 */
final readonly class EloquentShoppingListRepository implements ShoppingListRepository
{
    /**
     * Takes the connection whose transaction makes a save all or nothing.
     */
    public function __construct(private ConnectionInterface $connection) {}

    /**
     * Loads the owner's list with its items in order, or an empty list when none was saved.
     */
    public function forOwner(UserId $owner): ShoppingList
    {
        $record = ShoppingListRecord::query()->with('items')->where('user_id', $owner->value)->first();

        if ($record === null) {
            return ShoppingList::empty($owner);
        }

        return ShoppingList::restore($owner, ...$record->items->map($this->item(...))->all());
    }

    /**
     * Replaces the owner's stored items with the list's, in one transaction.
     */
    public function save(ShoppingList $list): void
    {
        $this->connection->transaction(static function () use ($list): void {
            $record = ShoppingListRecord::query()->firstOrCreate(['user_id' => $list->owner->value]);
            $record->items()->delete();
            $record->items()->createMany(array_map(
                static fn (ListItem $item, int $position): array => [
                    'position' => $position,
                    'product_id' => $item->productId->value,
                    'product_name' => $item->productName->value,
                ],
                $list->items(),
                array_keys($list->items()),
            ));
            $record->touch();
        });
    }

    /**
     * The domain item a row stands for.
     */
    private function item(ShoppingListItemRecord $record): ListItem
    {
        return new ListItem(new ProductId($record->product_id), ProductName::from($record->product_name));
    }
}
