<?php

declare(strict_types=1);

namespace App\Application\Account;

use SensitiveParameter;

/**
 * What the sign-up form sends, as typed; {@see RegisterAccount} checks it.
 */
final readonly class RegistrationInput
{
    /**
     * Holds the name, the email address and the password as typed.
     */
    public function __construct(
        public string $name,
        public string $email,
        #[SensitiveParameter] public string $password,
    ) {}
}
