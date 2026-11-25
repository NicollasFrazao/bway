<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Account\AuthoriseStoreManagement;
use App\Domain\Account\AccountRepository;
use App\Domain\Account\PasswordHasher;
use App\Domain\Shared\UserId;
use App\Infrastructure\Account\FrameworkPasswordHasher;
use App\Infrastructure\Persistence\EloquentAccountRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of the accounts: binds their ports and defines the abilities the routes check.
 */
final class AccountServiceProvider extends ServiceProvider
{
    /**
     * The ability the store-management routes require (`can:manage-stores`).
     */
    public const string MANAGE_STORES = 'manage-stores';

    /**
     * Binds the account storage and the password hasher.
     */
    public function register(): void
    {
        $this->app->bind(AccountRepository::class, EloquentAccountRepository::class);
        $this->app->singleton(PasswordHasher::class, FrameworkPasswordHasher::class);
    }

    /**
     * Defines `manage-stores`: granted to administrators only, as the domain's account decides.
     */
    public function boot(): void
    {
        Gate::define(
            self::MANAGE_STORES,
            fn (Authenticatable $user): bool => $this->app->make(AuthoriseStoreManagement::class)
                ->handle(new UserId($this->id($user))),
        );
    }

    /**
     * The integer id the session guard keeps for the signed-in user.
     *
     * @throws \UnexpectedValueException when the guard's user has no integer id.
     */
    private function id(Authenticatable $user): int
    {
        $id = $user->getAuthIdentifier();

        return is_int($id) ? $id : throw new \UnexpectedValueException('The signed-in user has no integer id.');
    }
}
