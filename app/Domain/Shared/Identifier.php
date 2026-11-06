<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * The identity of an entity: a positive integer assigned by the storage that keeps it.
 *
 * Each kind of entity has its own subclass, so a product id cannot be passed where a store id is
 * expected.
 */
abstract readonly class Identifier
{
    /**
     * Wraps the stored key.
     *
     * @throws InvalidIdentifier when the value is zero or negative.
     */
    final public function __construct(public int $value)
    {
        if ($value < 1) {
            throw InvalidIdentifier::notPositive(static::class, $value);
        }
    }
}
