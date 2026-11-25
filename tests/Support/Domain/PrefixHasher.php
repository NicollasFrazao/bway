<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Account\HashedPassword;
use App\Domain\Account\PasswordHasher;
use App\Domain\Account\PlainPassword;

/**
 * A hasher for unit tests: the "hash" is the password with a prefix, so tests read it, and it counts
 * the checks spent on unknown emails.
 */
final class PrefixHasher implements PasswordHasher
{
    /**
     * How many times {@see PrefixHasher::spendCheck()} ran.
     */
    public int $spentChecks = 0;

    /**
     * The password with `hashed:` in front.
     */
    public function hash(PlainPassword $password): HashedPassword
    {
        return new HashedPassword('hashed:'.$password->value);
    }

    /**
     * Tells whether the typed text is the password behind the prefix.
     */
    public function matches(string $typed, HashedPassword $hash): bool
    {
        return $hash->value === 'hashed:'.$typed;
    }

    /**
     * Counts the check.
     */
    public function spendCheck(string $typed): void
    {
        $this->spentChecks++;
    }
}
