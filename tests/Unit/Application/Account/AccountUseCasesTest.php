<?php

declare(strict_types=1);

use App\Application\Account\AuthoriseStoreManagement;
use App\Application\Account\RegisterAccount;
use App\Application\Account\RegistrationInput;
use App\Application\Account\ResetPassword;
use App\Application\Account\SignIn;
use App\Domain\Account\Account;
use App\Domain\Account\AccountField;
use App\Domain\Account\AccountNotFound;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\InvalidAccountData;
use App\Domain\Account\InvalidCredentials;
use App\Domain\Account\Role;
use App\Domain\Shared\UserId;
use Tests\Support\Domain\InMemoryAccountRepository;
use Tests\Support\Domain\PrefixHasher;

/**
 * Registers Ana (password `correct horse`) and gives back her id.
 */
function registerAna(InMemoryAccountRepository $accounts): UserId
{
    return new RegisterAccount($accounts, new PrefixHasher)
        ->handle(new RegistrationInput('  Ana   Souza ', 'Ana@Example.com', 'correct horse'));
}

it('registers a shopper with a normalised name and email and a hashed password', function (): void {
    $accounts = new InMemoryAccountRepository;

    $id = registerAna($accounts);

    $account = $accounts->get($id);
    expect($account->name->value)->toBe('Ana Souza')
        ->and($account->credentials->email->value)->toBe('ana@example.com')
        ->and($account->credentials->password->value)->toBe('hashed:correct horse')
        ->and($account->role)->toBe(Role::Shopper);
});

it('refuses an email that has an account already', function (): void {
    $accounts = new InMemoryAccountRepository;
    registerAna($accounts);

    registerAna($accounts);
})->throws(EmailAlreadyRegistered::class);

it('refuses each value it cannot accept, naming the field', function (RegistrationInput $input, AccountField $field): void {
    try {
        new RegisterAccount(new InMemoryAccountRepository, new PrefixHasher)->handle($input);
    } catch (InvalidAccountData $error) {
        expect($error->field)->toBe($field);

        return;
    }

    throw new LogicException('The sign-up was accepted.');
})->with([
    'blank name' => [new RegistrationInput('  ', 'ana@example.com', 'correct horse'), AccountField::Name],
    'long name' => [new RegistrationInput(str_repeat('a', 101), 'ana@example.com', 'correct horse'), AccountField::Name],
    'bad email' => [new RegistrationInput('Ana', 'ana', 'correct horse'), AccountField::Email],
    'short password' => [new RegistrationInput('Ana', 'ana@example.com', 'short'), AccountField::Password],
]);

it('signs in with the email in any case and the right password', function (): void {
    $accounts = new InMemoryAccountRepository;
    $id = registerAna($accounts);

    expect(new SignIn($accounts, new PrefixHasher)->handle(' ANA@example.com', 'correct horse'))->toEqual($id);
});

it('refuses a wrong password, an unknown email and a malformed one the same way', function (string $email, string $password, int $spent): void {
    $accounts = new InMemoryAccountRepository;
    registerAna($accounts);
    $hasher = new PrefixHasher;

    expect(static fn (): UserId => new SignIn($accounts, $hasher)->handle($email, $password))
        ->toThrow(InvalidCredentials::class, 'The email or the password is wrong.')
        ->and($hasher->spentChecks)->toBe($spent);
})->with([
    'wrong password' => ['ana@example.com', 'wrong horse', 0],
    'unknown email, one hash check spent' => ['bia@example.com', 'correct horse', 1],
    'malformed email, one hash check spent' => ['ana', 'correct horse', 1],
]);

it('resets the password to a new hash', function (): void {
    $accounts = new InMemoryAccountRepository;
    $id = registerAna($accounts);

    new ResetPassword($accounts, new PrefixHasher)->handle($id, 'new password');

    expect($accounts->get($id)->credentials->password->value)->toBe('hashed:new password');
});

it('refuses a new password that is too short and keeps the old one', function (): void {
    $accounts = new InMemoryAccountRepository;
    $id = registerAna($accounts);

    expect(static fn () => new ResetPassword($accounts, new PrefixHasher)->handle($id, 'short'))
        ->toThrow(InvalidAccountData::class)
        ->and($accounts->get($id)->credentials->password->value)->toBe('hashed:correct horse');
});

it('authorises store management for administrators only', function (): void {
    $accounts = new InMemoryAccountRepository;
    $shopper = registerAna($accounts);
    $ana = $accounts->get($shopper);
    $accounts->save(new Account($ana->id, $ana->name, $ana->credentials, Role::Administrator));
    $bia = new RegisterAccount($accounts, new PrefixHasher)->handle(new RegistrationInput('Bia', 'bia@example.com', 'correct horse'));
    $authorise = new AuthoriseStoreManagement($accounts);

    expect($authorise->handle($shopper))->toBeTrue()
        ->and($authorise->handle($bia))->toBeFalse();
});

it('does not authorise an account that does not exist', function (): void {
    new AuthoriseStoreManagement(new InMemoryAccountRepository)->handle(new UserId(7));
})->throws(AccountNotFound::class);
