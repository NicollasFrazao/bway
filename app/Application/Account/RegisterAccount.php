<?php

declare(strict_types=1);

namespace App\Application\Account;

use App\Domain\Account\Account;
use App\Domain\Account\AccountName;
use App\Domain\Account\AccountRepository;
use App\Domain\Account\Credentials;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\InvalidAccountData;
use App\Domain\Account\NewAccount;
use App\Domain\Account\PasswordHasher;
use App\Domain\Account\PlainPassword;
use App\Domain\Shared\UserId;

/**
 * Signs a person up as a shopper, with the password hashed (use case).
 */
final readonly class RegisterAccount
{
    /**
     * Takes the accounts and the password hasher.
     */
    public function __construct(
        private AccountRepository $accounts,
        private PasswordHasher $hasher,
    ) {}

    /**
     * Registers the account the form describes.
     *
     * @throws InvalidAccountData when the name, the email or the password is refused.
     * @throws EmailAlreadyRegistered when another account has the email address.
     */
    public function handle(RegistrationInput $input): UserId
    {
        $email = EmailAddress::from($input->email);
        $password = PlainPassword::from($input->password);
        $name = AccountName::from($input->name);

        if ($this->accounts->findByEmail($email) instanceof Account) {
            throw EmailAlreadyRegistered::create();
        }

        return $this->accounts->register(
            new NewAccount($name, new Credentials($email, $this->hasher->hash($password))),
        );
    }
}
