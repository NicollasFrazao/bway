<?php

declare(strict_types=1);

use App\Application\ShoppingList\AddProductToList;
use App\Application\ShoppingList\ClearShoppingList;
use App\Application\ShoppingList\RemoveProductFromList;
use App\Application\ShoppingList\SearchProducts;
use App\Application\ShoppingList\ShowShoppingList;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\DuplicateListItem;
use App\Domain\ShoppingList\ListItem;
use App\Domain\ShoppingList\ProductNotInList;
use Tests\Support\Domain\InMemoryProductCatalog;
use Tests\Support\Domain\InMemoryShoppingListRepository;

/**
 * A catalogue of rice (1), beans (2) and bananas (3).
 */
function pantry(): InMemoryProductCatalog
{
    return new InMemoryProductCatalog(
        new Product(new ProductId(1), ProductName::from('rice')),
        new Product(new ProductId(2), ProductName::from('Beans')),
        new Product(new ProductId(3), ProductName::from('Bananas')),
    );
}

/**
 * The names of the items the use case shows to the owner.
 *
 * @return list<string>
 */
function shownNames(InMemoryShoppingListRepository $lists, int $owner): array
{
    return array_map(
        static fn (ListItem $item): string => $item->productName->value,
        new ShowShoppingList($lists)->handle(new UserId($owner)),
    );
}

it('adds products to the owner\'s list and shows them by name, ignoring case', function (): void {
    $lists = new InMemoryShoppingListRepository;
    $add = new AddProductToList($lists, pantry());

    foreach ([1, 2, 3] as $product) {
        $add->handle(new UserId(4), new ProductId($product));
    }

    expect(shownNames($lists, 4))->toBe(['Bananas', 'Beans', 'rice'])
        ->and(shownNames($lists, 5))->toBe([]);
});

it('refuses a product the catalogue does not have and one already on the list', function (): void {
    $lists = new InMemoryShoppingListRepository;
    $add = new AddProductToList($lists, pantry());
    $add->handle(new UserId(4), new ProductId(1));

    expect(static fn () => $add->handle(new UserId(4), new ProductId(9)))->toThrow(ProductNotFound::class)
        ->and(static fn () => $add->handle(new UserId(4), new ProductId(1)))->toThrow(DuplicateListItem::class);
});

it('removes a product from the owner\'s list only', function (): void {
    $lists = new InMemoryShoppingListRepository;
    $add = new AddProductToList($lists, pantry());
    $add->handle(new UserId(4), new ProductId(1));
    $add->handle(new UserId(5), new ProductId(1));

    new RemoveProductFromList($lists)->handle(new UserId(4), new ProductId(1));

    expect(shownNames($lists, 4))->toBe([])
        ->and(shownNames($lists, 5))->toBe(['rice']);
});

it('refuses to remove a product that is not on the list', function (): void {
    new RemoveProductFromList(new InMemoryShoppingListRepository)->handle(new UserId(4), new ProductId(1));
})->throws(ProductNotInList::class);

it('clears the owner\'s list only', function (): void {
    $lists = new InMemoryShoppingListRepository;
    $add = new AddProductToList($lists, pantry());
    $add->handle(new UserId(4), new ProductId(1));
    $add->handle(new UserId(5), new ProductId(2));

    new ClearShoppingList($lists)->handle(new UserId(4));

    expect(shownNames($lists, 4))->toBe([])
        ->and(shownNames($lists, 5))->toBe(['Beans']);
});

it('searches the catalogue, and finds nothing for a blank term', function (): void {
    $search = new SearchProducts(pantry());

    expect(array_map(static fn (Product $product): string => $product->name->value, $search->handle(' ba ')))->toBe(['Bananas'])
        ->and($search->handle('   '))->toBe([]);
});
