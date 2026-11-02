<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('serves the welcome page', function (): void {
    get('/')->assertOk()->assertSee('<title>bway', escape: false);
});

it('answers the health check used by the container', function (): void {
    get('/up')->assertOk();
});
