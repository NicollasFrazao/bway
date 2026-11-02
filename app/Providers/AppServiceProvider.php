<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Composition root: the one place that binds the ports of the inner layers to their adapters.
 *
 * Empty until the first port exists (the repositories of issue #7); it may depend on every layer,
 * and no other class may.
 */
final class AppServiceProvider extends ServiceProvider {}
