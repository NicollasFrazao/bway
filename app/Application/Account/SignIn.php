<?php

declare(strict_types=1);

namespace App\Application\Account;

use App\Domain\Account\Account;
use App\Domain\Account\AccountRepository;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\InvalidAccountData;
use App\Domain\Account\InvalidCredentials;
use App\Domain\Account\PasswordHasher;
use App\Domain\Shared\UserId;
use SensitiveParameter;

/**
 * Checks an email address and a password and says whose account they open (use case).
 *
 * The 2020 version compared the password in plain text in the query; here the hash is checked, and an unknown
 * email costs one hash check too, so the time taken does not tell which emails have an account.
 */
final readonly class SignIn
{
    /**
     * Takes the accounts and the password hasher.
     */
    public function __construct(
        private AccountRepository $accounts,
        private PasswordHasher $hasher,
    ) {}

    /**
     * The account the email and the password open.
     *
     * @throws InvalidCredentials when no account has the email or the password is not its password.
     */
    public function handle(string $email, #[SensitiveParameter] string $password): UserId
    {
        try {
            $account = $this->accounts->findByEmail(EmailAddress::from($email));
        } catch (InvalidAccountData) {
            $account = null;
        }

        if (! $account instanceof Account) {
            $this->hasher->spendCheck($password);

            throw InvalidCredentials::create();
        }

        if (! $this->hasher->matches($password, $account->credentials->password)) {
            throw InvalidCredentials::create();
        }

        return $account->id;
    }
}
