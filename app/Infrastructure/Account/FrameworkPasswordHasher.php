<?php

declare(strict_types=1);

namespace App\Infrastructure\Account;

use App\Domain\Account\HashedPassword;
use App\Domain\Account\PasswordHasher;
use App\Domain\Account\PlainPassword;
use Illuminate\Contracts\Hashing\Hasher;
use SensitiveParameter;

/**
 * Passwords hashed with the framework's hasher (bcrypt, `config/hashing.php`).
 */
final class FrameworkPasswordHasher implements PasswordHasher
{
    /**
     * The random bytes the decoy hash is made from.
     */
    private const int DECOY_BYTES = 16;

    /**
     * A hash of a random text, made once per process, that no typed password matches.
     */
    private ?string $decoy = null;

    /**
     * Takes the framework's hasher.
     */
    public function __construct(private readonly Hasher $hasher) {}

    /**
     * The bcrypt hash of a new password.
     */
    public function hash(PlainPassword $password): HashedPassword
    {
        return new HashedPassword($this->hasher->make($password->value));
    }

    /**
     * Tells whether the typed text is the password of the hash.
     */
    public function matches(#[SensitiveParameter] string $typed, HashedPassword $hash): bool
    {
        return $this->hasher->check($typed, $hash->value);
    }

    /**
     * Checks the typed text against a hash of random text, made once per process.
     */
    public function spendCheck(#[SensitiveParameter] string $typed): void
    {
        $this->decoy ??= $this->hasher->make(bin2hex(random_bytes(self::DECOY_BYTES)));
        $this->hasher->check($typed, $this->decoy);
    }
}
