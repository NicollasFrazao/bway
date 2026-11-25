<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('serves the home page, with the way in for a guest', function (): void {
    get('/')->assertOk()
        ->assertSee('<title>Shop by the shortest walk', escape: false)
        ->assertSee('/register', escape: false)
        ->assertSee('/login', escape: false);
});

it('answers the health check used by the container', function (): void {
    get('/up')->assertOk();
});
