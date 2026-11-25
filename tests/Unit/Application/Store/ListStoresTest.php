<?php

declare(strict_types=1);

use App\Application\Store\ListStores;
use Tests\Support\AisleShop;
use Tests\Support\CornerShop;
use Tests\Support\Domain\InMemoryStoreRepository;

it('lists every store', function (): void {
    $stores = new InMemoryStoreRepository(CornerShop::store(), AisleShop::store());

    expect(new ListStores($stores)->handle())->toEqual($stores->all());
});
