<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Catalog\ProductCatalog;
use App\Domain\Routing\RouteCache;
use App\Domain\ShoppingList\ShoppingListRepository;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreRepository;
use App\Infrastructure\Persistence\EloquentAssortmentRepository;
use App\Infrastructure\Persistence\EloquentProductCatalog;
use App\Infrastructure\Persistence\EloquentRouteCache;
use App\Infrastructure\Persistence\EloquentShoppingListRepository;
use App\Infrastructure\Persistence\EloquentStoreGridRepository;
use App\Infrastructure\Persistence\EloquentStoreRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of the persistence: binds every repository port of the domain to its Eloquent
 * adapter on the default (MySQL) connection.
 *
 * Grids and routes are rows too (docs/adr/0005-persistence.md), so a store, its layout, its
 * assortment, its grid and its routes live and are deleted together.
 */
final class PersistenceServiceProvider extends ServiceProvider
{
    /**
     * Binds the catalogue, the shopping lists, the stores, their assortments, grids and routes.
     */
    public function register(): void
    {
        $this->app->bind(ProductCatalog::class, EloquentProductCatalog::class);
        $this->app->bind(ShoppingListRepository::class, EloquentShoppingListRepository::class);
        $this->app->bind(StoreRepository::class, EloquentStoreRepository::class);
        $this->app->bind(AssortmentRepository::class, EloquentAssortmentRepository::class);
        $this->app->bind(StoreGridRepository::class, EloquentStoreGridRepository::class);
        $this->app->bind(RouteCache::class, EloquentRouteCache::class);
    }
}
