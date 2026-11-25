<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\PersistenceServiceProvider;
use App\Providers\VisitServiceProvider;

return [
    AppServiceProvider::class,
    PersistenceServiceProvider::class,
    VisitServiceProvider::class,
];
