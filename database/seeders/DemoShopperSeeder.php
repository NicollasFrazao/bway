<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\ProductCatalog;
use App\Domain\Catalog\ProductId;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a user to sign in with, `test@example.com` (password `password`), and a shopping list that
 * needs every sales sector of store 1 and one product store 1 does not sell.
 */
final class DemoShopperSeeder extends Seeder
{
    /**
     * The e-mail address of the demo user.
     */
    public const string EMAIL = 'test@example.com';

    /**
     * The demo list, in the order the products are added.
     */
    public const array LIST = ['Rice', 'Bread loaf', 'Milk', 'Dish soap', 'Soft drinks', 'Batteries', 'Bananas'];

    /**
     * Creates the user and saves the list through the domain, as the app would.
     */
    public function run(ProductCatalog $catalog, ShoppingListRepository $lists): void
    {
        $user = User::factory()->create(['name' => 'Test User', 'email' => self::EMAIL]);
        $list = ShoppingList::empty(new UserId($user->id));

        foreach (self::LIST as $name) {
            $record = ProductRecord::query()->where('name', $name)->firstOrFail();
            $list->add($catalog->get(new ProductId($record->id)));
        }

        $lists->save($list);
    }
}
