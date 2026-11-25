<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * Runs work that changes several things as one: all of it is kept, or none (port; implemented in
 * Infrastructure on the database's transactions).
 */
interface Transaction
{
    /**
     * Runs the work and gives its result; an exception it throws undoes what it changed and is thrown
     * again.
     *
     * @template TResult
     *
     * @param  callable(): TResult  $work  What to run.
     * @return TResult
     */
    public function run(callable $work): mixed;
}
