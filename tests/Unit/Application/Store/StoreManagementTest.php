<?php

declare(strict_types=1);

use App\Application\Routing\GenerateStoreRoutes;
use App\Application\Store\AreaInput;
use App\Application\Store\EditAssortment;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductName;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\Grid\EmptyGridArea;
use App\Domain\Routing\Route;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Store\LayoutOutsideGrid;
use App\Domain\Store\OverlappingLayout;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreWithoutEntrance;
use Tests\Support\Domain\InMemoryAssortmentRepository;
use Tests\Support\Domain\InMemoryProductCatalog;
use Tests\Support\StoreKit;

/**
 * The names of the store's sectors.
 *
 * @return list<string>
 */
function sectorsOf(StoreKit $kit, StoreId $id): array
{
    return array_map(static fn (Sector $sector): string => $sector->name->value, $kit->stores->get($id)->sectors());
}

it('creates a store with its entrance and its mapped grid', function (): void {
    $kit = new StoreKit;

    $id = $kit->create();

    expect(sectorsOf($kit, $id))->toBe(['Entrance'])
        ->and($kit->stores->get($id)->name->value)->toBe('U shop')
        ->and($kit->grids->forStore($id)->width)->toBe(7);
});

it('refuses an empty entrance before writing anything, and one outside the plan', function (): void {
    $kit = new StoreKit;

    // Undoing the store written before the plan refused the entrance is the transaction's: the
    // feature tests run it on MySQL.
    expect(static fn (): StoreId => $kit->create('U shop', new AreaInput(1, 1, 0, 1)))->toThrow(EmptyGridArea::class)
        ->and($kit->stores->all())->toBe([])
        ->and(static fn (): StoreId => $kit->create('U shop', new AreaInput(6, 6, 2, 2)))->toThrow(LayoutOutsideGrid::class);
});

it('adds, moves and removes sectors and partitions, forgetting the routes each time', function (): void {
    $kit = new StoreKit;
    $id = $kit->create();
    $bakery = $kit->sectors()->add($id, 'Bakery', new AreaInput(5, 5, 1, 1));
    $kit->find()->handle($id, new SectorId(1), $bakery);

    $kit->sectors()->update($id, $bakery, 'Bread', new AreaInput(5, 4, 1, 1));
    $partition = $kit->partitions()->add($id, new AreaInput(3, 3, 1, 1));

    expect(sectorsOf($kit, $id))->toBe(['Entrance', 'Bread'])
        ->and($kit->stores->get($id)->sector($bakery)->area->origin->y)->toBe(4)
        ->and($kit->routes->countFound($id))->toBe(0)
        ->and($kit->stores->get($id)->partitions())->toHaveCount(1);

    $kit->partitions()->remove($id, $partition);
    $kit->sectors()->remove($id, $bakery);

    expect(sectorsOf($kit, $id))->toBe(['Entrance'])
        ->and($kit->stores->get($id)->partitions())->toBe([]);
});

it('refuses a layout the domain refuses, and keeps the old one', function (): void {
    $kit = new StoreKit;
    $id = $kit->create();

    expect(static fn (): SectorId => $kit->sectors()->add($id, 'On the entrance', new AreaInput(1, 1, 1, 1)))->toThrow(OverlappingLayout::class)
        ->and(static fn (): SectorId => $kit->sectors()->add($id, 'Outside', new AreaInput(6, 6, 2, 1)))->toThrow(LayoutOutsideGrid::class)
        ->and(static fn () => $kit->sectors()->remove($id, new SectorId(1)))->toThrow(StoreWithoutEntrance::class)
        ->and(static fn () => $kit->sectors()->remove($id, new SectorId(9)))->toThrow(SectorNotInStore::class)
        ->and(sectorsOf($kit, $id))->toBe(['Entrance']);
});

it('finds every route, keeps the pairs without one, and reports the progress', function (): void {
    $kit = new StoreKit;
    $id = $kit->create();
    $kit->sectors()->add($id, 'Corner', new AreaInput(5, 5, 1, 1));
    $kit->sectors()->add($id, 'Island', new AreaInput(3, 3, 1, 1));
    $tracking = $kit->tracking();

    $before = $tracking->progress($id);
    $tracking->start($id);
    new GenerateStoreRoutes($kit->stores, $kit->find(), $kit->routes)->handle($id);
    $after = $tracking->progress($id);

    expect([$before->total, $before->found, $before->isComplete()])->toBe([6, 0, false])
        ->and($kit->queue->pushed)->toBe([$id->value])
        ->and($after->found)->toBe(2)
        ->and($after->isComplete())->toBeTrue()
        ->and(array_map(static fn (array $pair): string => $pair[0]->name->value.' > '.$pair[1]->name->value, $after->unreachable))
        ->toBe(['Entrance > Island', 'Corner > Island', 'Island > Entrance', 'Island > Corner']);
});

it('places products in sectors, and leaves out those without one', function (): void {
    $kit = new StoreKit;
    $id = $kit->create();
    $bakery = $kit->sectors()->add($id, 'Bakery', new AreaInput(5, 5, 1, 1));
    $assortments = new InMemoryAssortmentRepository;
    $catalog = new InMemoryProductCatalog(
        new Product(new ProductId(1), ProductName::from('Bread')),
        new Product(new ProductId(2), ProductName::from('Milk')),
    );
    $edit = new EditAssortment($assortments, $kit->stores, $catalog);

    $edit->save($id, [1 => $bakery->value]);
    $view = $edit->show($id);

    expect($view->assortment->sectorSelling(new ProductId(1)))->toEqual($bakery)
        ->and($view->assortment->sectorSelling(new ProductId(2)))->toBeNull()
        ->and($view->products)->toHaveCount(2)
        ->and(static fn () => $edit->save($id, [1 => 99]))->toThrow(SectorNotInStore::class)
        ->and(static fn () => $edit->save($id, [9 => $bakery->value]))->toThrow(ProductNotFound::class);
});

it('answers a pair known to have no route without searching again', function (): void {
    $kit = new StoreKit;
    $id = $kit->create();
    $island = $kit->sectors()->add($id, 'Island', new AreaInput(3, 3, 1, 1));
    $kit->routes->rememberUnreachable($id, new SectorId(1), $island);

    expect(static fn (): Route => $kit->find()->handle($id, new SectorId(1), $island))->toThrow(RouteNotFound::class)
        ->and($kit->routes->countFound($id))->toBe(0);
});
