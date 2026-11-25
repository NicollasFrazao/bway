<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Grid\GridPainter;
use App\Domain\Routing\RouteGenerationQueue;
use App\Domain\Shared\Transaction;
use App\Infrastructure\FloorPlan\GdGridPainter;
use App\Infrastructure\Persistence\DatabaseTransaction;
use App\Infrastructure\Queue\LaravelRouteGenerationQueue;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of store management (B7): the transaction, the grid painter and the queue that
 * finds a store's routes.
 */
final class StoreManagementServiceProvider extends ServiceProvider
{
    /**
     * Binds the ports store management needs besides the repositories.
     */
    public function register(): void
    {
        $this->app->bind(
            Transaction::class,
            static fn (Application $app): Transaction => new DatabaseTransaction($app->make(ConnectionInterface::class)),
        );
        $this->app->bind(GridPainter::class, GdGridPainter::class);
        $this->app->bind(RouteGenerationQueue::class, LaravelRouteGenerationQueue::class);
    }
}
