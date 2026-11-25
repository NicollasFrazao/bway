<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\StoreRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds a fresh database so the whole flow works: the catalogue, store 1 with its layout, assortment
 * and mapped floor plan, and a demo user with a shopping list.
 *
 * The app container runs it on every start (`migrate --seed`), so it does nothing once store 1 exists.
 */
final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Runs the seeders in dependency order, unless the database was seeded already.
     */
    public function run(): void
    {
        if (StoreRecord::query()->whereKey(StoreOneLayout::STORE_ID)->exists()) {
            $this->command?->info('Store 1 exists already; nothing seeded.');

            return;
        }

        $this->call([ProductCatalogSeeder::class, StoreOneSeeder::class, DemoShopperSeeder::class]);
    }
}
