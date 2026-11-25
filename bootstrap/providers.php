<?php

declare(strict_types=1);

use App\Providers\AccountServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\PersistenceServiceProvider;
use App\Providers\StoreManagementServiceProvider;
use App\Providers\VisitServiceProvider;

return [
    AccountServiceProvider::class,
    AppServiceProvider::class,
    PersistenceServiceProvider::class,
    StoreManagementServiceProvider::class,
    VisitServiceProvider::class,
];
