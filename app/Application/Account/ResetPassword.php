<?php

declare(strict_types=1);

namespace App\Application\Account;

use App\Domain\Account\AccountNotFound;
use App\Domain\Account\AccountRepository;
use App\Domain\Account\InvalidAccountData;
use App\Domain\Account\PasswordHasher;
use App\Domain\Account\PlainPassword;
use App\Domain\Shared\UserId;
use SensitiveParameter;

/**
 * Gives an account a new password, once the edge has checked the reset link (use case).
 */
final readonly class ResetPassword
{
    /**
     * Takes the accounts and the password hasher.
     */
    public function __construct(
        private AccountRepository $accounts,
        private PasswordHasher $hasher,
    ) {}

    /**
     * Hashes and stores the new password.
     *
     * @throws InvalidAccountData when the new password is refused.
     * @throws AccountNotFound when no account has the id.
     */
    public function handle(UserId $id, #[SensitiveParameter] string $password): void
    {
        $hash = $this->hasher->hash(PlainPassword::from($password));

        $this->accounts->save($this->accounts->get($id)->withPassword($hash));
    }
}
