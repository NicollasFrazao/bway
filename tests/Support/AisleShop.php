<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Shared\Name;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\ShoppingList;
use App\Domain\Store\Assortment;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorKind;
use App\Domain\Store\SectorProduct;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;

/**
 * Store 1 as one aisle, where going to the nearest sector first is not the shortest visit: the
 * entrance (sector 1) is in the middle, the bakery (2) one step below it, the dairy (3) two steps
 * above, the produce (4) four steps below. Walking a list of all three, nearest neighbour goes
 * bakery, dairy, produce (1 + 3 + 6 = 10 steps; from the bakery the dairy and the produce are both
 * 3 steps away, and the lower id wins); the shortest is dairy, bakery, produce (2 + 3 + 3 = 8).
 *
 *     RRR      the floor plan, 3 x 9 pixels (portrait, so the mapper does not turn it)
 *     ROR      dairy (1, 1)
 *     ROR
 *     ROR      entrance (1, 3)
 *     ROR      bakery (1, 4)
 *     ROR
 *     ROR
 *     ROR      produce (1, 7)
 *     RRR
 */
final class AisleShop
{
    /**
     * The products of the tests, by name: one per sector, a second one in the dairy, and batteries,
     * which the store does not sell.
     */
    private const array PRODUCTS = [
        'Bread' => 1,
        'Milk' => 2,
        'Cheese' => 3,
        'Apples' => 4,
        'Batteries' => 5,
    ];

    /**
     * The store.
     */
    public static function store(): Store
    {
        return new Store(new StoreId(1), Name::from('Aisle shop'), [
            self::sector(1, 'Entrance', 3, SectorKind::Entrance),
            self::sector(2, 'Bakery', 4, SectorKind::Sales),
            self::sector(3, 'Dairy', 1, SectorKind::Sales),
            self::sector(4, 'Produce', 7, SectorKind::Sales),
        ], []);
    }

    /**
     * Its floor plan, as a text file TextFloorPlanDecoder reads.
     */
    public static function floorPlan(): string
    {
        return implode("\n", ['RRR', ...array_fill(0, 7, 'ROR'), 'RRR']);
    }

    /**
     * What it sells: bread in the bakery, milk and cheese in the dairy, apples in the produce.
     */
    public static function assortment(): Assortment
    {
        return new Assortment(
            new StoreId(1),
            new SectorProduct(new SectorId(2), self::product('Bread')->id),
            new SectorProduct(new SectorId(3), self::product('Milk')->id),
            new SectorProduct(new SectorId(3), self::product('Cheese')->id),
            new SectorProduct(new SectorId(4), self::product('Apples')->id),
        );
    }

    /**
     * The list of user 1 with the named products, in that order.
     */
    public static function listOf(string ...$names): ShoppingList
    {
        $list = ShoppingList::empty(new UserId(1));

        foreach ($names as $name) {
            $list->add(self::product($name));
        }

        return $list;
    }

    /**
     * One of the products of the tests.
     */
    public static function product(string $name): Product
    {
        return new Product(new ProductId(self::PRODUCTS[$name] ?? 0), ProductName::from($name));
    }

    /**
     * A one-cell sector on the middle column.
     */
    private static function sector(int $id, string $name, int $y, SectorKind $kind): Sector
    {
        return new Sector(new SectorId($id), Name::from($name), new GridArea(new GridCoordinate(1, $y), 1, 1), $kind);
    }
}
