<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\FloorPlan\InvalidColor;
use App\Domain\FloorPlan\UnreadableFloorPlan;
use App\Domain\FloorPlan\WallFrameNotFound;
use App\Domain\Grid\CoordinateOutsideGrid;
use App\Domain\Grid\EmptyGrid;
use App\Domain\Grid\EmptyGridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Grid\NegativeCoordinate;
use App\Domain\Grid\RaggedGrid;
use App\Domain\Routing\BrokenRoute;
use App\Domain\Routing\RouteNotFound;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\InvalidIdentifier;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\DuplicateListItem;
use App\Domain\ShoppingList\ProductNotInList;
use App\Domain\Store\DuplicatePartition;
use App\Domain\Store\DuplicateSector;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\LayoutOutsideGrid;
use App\Domain\Store\OverlappingLayout;
use App\Domain\Store\PartitionId;
use App\Domain\Store\ProductInSeveralSectors;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreWithoutEntrance;
use App\Domain\Store\StoreWithSeveralEntrances;
use Tests\Support\GridDrawing;

it('makes every domain exception a domain error, so the outer layers can catch them as one', function (Throwable $error): void {
    expect($error)->toBeInstanceOf(DomainError::class);
})->with([
    'invalid identifier' => [InvalidIdentifier::notPositive(UserId::class, 0)],
    'blank name' => [InvalidName::blank()],
    'negative coordinate' => [NegativeCoordinate::at(-1, 0)],
    'empty grid area' => [EmptyGridArea::withSize(0, 1)],
    'product not found' => [ProductNotFound::withId(new ProductId(1))],
    'duplicate list item' => [DuplicateListItem::forProduct(new ProductId(1))],
    'product not in list' => [ProductNotInList::forProduct(new ProductId(1))],
    'store not found' => [StoreNotFound::withId(new StoreId(1))],
    'store without entrance' => [StoreWithoutEntrance::forStore(new StoreId(1))],
    'store with several entrances' => [StoreWithSeveralEntrances::forStore(new StoreId(1))],
    'duplicate sector' => [DuplicateSector::inStore(new StoreId(1), new SectorId(1))],
    'duplicate partition' => [DuplicatePartition::inStore(new StoreId(1), new PartitionId(1))],
    'sector not in store' => [SectorNotInStore::forSector(new StoreId(1), new SectorId(1))],
    'product in several sectors' => [ProductInSeveralSectors::inStore(new StoreId(1), new ProductId(1), new SectorId(1), new SectorId(2))],
    'overlapping layout' => [OverlappingLayout::between(new StoreId(1), 'sector 1', 'sector 2')],
    'layout outside grid' => [LayoutOutsideGrid::forPart(new StoreId(1), 'sector 1', GridDrawing::grid(['.']))],
    'grid not mapped' => [GridNotMapped::forStore(new StoreId(1))],
    'empty grid' => [EmptyGrid::withoutCells()],
    'ragged grid' => [RaggedGrid::atRow(1, 2, 3)],
    'coordinate outside grid' => [CoordinateOutsideGrid::at(new GridCoordinate(5, 5), 1, 1)],
    'invalid color' => [InvalidColor::withChannels(256, 0, 0)],
    'unreadable floor plan' => [UnreadableFloorPlan::ofBytes(0)],
    'wall frame not found' => [WallFrameNotFound::inImage(1, 1)],
    'broken route' => [BrokenRoute::withoutCells()],
    'route not found' => [RouteNotFound::between(new GridCoordinate(0, 0), new GridCoordinate(1, 1))],
]);
