<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductId;
use App\Domain\Catalog\ProductNotFound;
use App\Domain\Grid\EmptyGridArea;
use App\Domain\Grid\NegativeCoordinate;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\InvalidIdentifier;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\UserId;
use App\Domain\ShoppingList\DuplicateListItem;
use App\Domain\ShoppingList\ProductNotInList;
use App\Domain\Store\DuplicatePartition;
use App\Domain\Store\DuplicateSector;
use App\Domain\Store\PartitionId;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorNotInStore;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreWithoutEntrance;
use App\Domain\Store\StoreWithSeveralEntrances;

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
]);
