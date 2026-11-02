<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds a local database with a user to sign in with; the store and the catalogue arrive with issue #7.
 */
final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Creates the user `test@example.com` (password `password`).
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
