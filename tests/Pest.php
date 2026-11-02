<?php

declare(strict_types=1);

use Illuminate\Testing\PendingCommand;
use Tests\Support\ScratchRepository;
use Tests\TestCase;

use function Pest\Laravel\artisan;

/*
 * Feature tests boot the application; unit tests are plain PHP and must stay that way, so the domain
 * tests of the next phases run without Laravel.
 */
pest()->extend(TestCase::class)->in('Feature');

afterEach(function (): void {
    ScratchRepository::deleteAll();
});

/**
 * Runs an Artisan command with its output captured, so the test can assert on it.
 *
 * @throws LogicException when console output mocking is off, which no test here does.
 */
function command(string $command): PendingCommand
{
    $pending = artisan($command);

    if (! $pending instanceof PendingCommand) {
        throw new LogicException('Console output mocking is off; the command cannot be asserted on.');
    }

    return $pending;
}
