<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Shared\Transaction;
use Illuminate\Database\ConnectionInterface;

/**
 * Work run in one transaction of the default database connection.
 */
final readonly class DatabaseTransaction implements Transaction
{
    /**
     * Takes the connection.
     */
    public function __construct(private ConnectionInterface $connection) {}

    /**
     * Runs the work in a transaction, committed when it returns and rolled back when it throws.
     *
     * @template TResult
     *
     * @param  callable(): TResult  $work  What to run.
     * @return TResult
     */
    public function run(callable $work): mixed
    {
        return $this->connection->transaction($work(...));
    }
}
