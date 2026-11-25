<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Shared\Transaction;

/**
 * Runs the work at once: in memory there is nothing to commit or roll back.
 */
final readonly class ImmediateTransaction implements Transaction
{
    /**
     * Runs the work and gives its result.
     *
     * @template TResult
     *
     * @param  callable(): TResult  $work  What to run.
     * @return TResult
     */
    public function run(callable $work): mixed
    {
        return $work();
    }
}
