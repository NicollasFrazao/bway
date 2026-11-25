<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Database\Seeder;

/**
 * Seeds an administrator to sign in with, `admin@example.com` (password `password`), who may open the
 * store management.
 */
final class DemoAdministratorSeeder extends Seeder
{
    /**
     * The e-mail address of the demo administrator.
     */
    public const string EMAIL = 'admin@example.com';

    /**
     * Creates the administrator.
     */
    public function run(): void
    {
        User::factory()->administrator()->create(['name' => 'Admin User', 'email' => self::EMAIL]);
    }
}
