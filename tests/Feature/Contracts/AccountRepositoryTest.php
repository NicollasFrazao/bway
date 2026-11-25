<?php

declare(strict_types=1);

use App\Domain\Account\Account;
use App\Domain\Account\AccountNotFound;
use App\Domain\Account\AccountRepository;
use App\Domain\Account\Credentials;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\HashedPassword;
use App\Domain\Account\NewAccount;
use App\Domain\Account\Role;
use App\Domain\Shared\Name;
use App\Domain\Shared\UserId;
use App\Infrastructure\Persistence\EloquentAccountRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Domain\InMemoryAccountRepository;

uses(RefreshDatabase::class);

/*
 * The contract every AccountRepository keeps. The in-memory adapter is the reference; the Eloquent
 * adapter runs it on MySQL.
 */
dataset('account repositories', [
    'in memory' => fn (): AccountRepository => new InMemoryAccountRepository,
    'eloquent' => fn (): AccountRepository => new EloquentAccountRepository,
]);

/**
 * A bcrypt hash (cost 4, as `phpunit.xml` sets) of `first` or `second`: the user model keeps a hash it
 * is given and hashes anything else, so the contract uses real hashes.
 *
 * @param  'first'|'second'  $text  The password hashed.
 */
function bcryptOf(string $text): HashedPassword
{
    return new HashedPassword(match ($text) {
        'first' => '$2y$04$RssHrF.3CaTxz7GfOb4NO.R9sBgAtKFqxHhe.GGNe1qQ3SPyfe9zu',
        'second' => '$2y$04$B1Sb48tGp/jf7Bjh8u6HXOZc25Ze1GBlr2L9WKWsefBsM2Q7zk0EK',
    });
}

/**
 * Ana's sign-up, with the hash of her password.
 */
function anaSignUp(string $email = 'ana@example.com'): NewAccount
{
    return new NewAccount(Name::from('Ana Souza'), new Credentials(EmailAddress::from($email), bcryptOf('first')));
}

it('registers a shopper and loads it by id and by email', function (AccountRepository $repository): void {
    $id = $repository->register(anaSignUp());

    $expected = new Account($id, Name::from('Ana Souza'), new Credentials(EmailAddress::from('ana@example.com'), bcryptOf('first')), Role::Shopper);

    expect($repository->get($id))->toEqual($expected)
        ->and($repository->findByEmail(EmailAddress::from('ANA@example.com')))->toEqual($expected)
        ->and($repository->findByEmail(EmailAddress::from('bia@example.com')))->toBeNull();
})->with('account repositories');

it('refuses a second account with the same email', function (AccountRepository $repository): void {
    $repository->register(anaSignUp());

    $repository->register(anaSignUp());
})->with('account repositories')->throws(EmailAlreadyRegistered::class);

it('saves a new password', function (AccountRepository $repository): void {
    $id = $repository->register(anaSignUp());

    $repository->save($repository->get($id)->withPassword(bcryptOf('second')));

    expect($repository->get($id)->credentials->password)->toEqual(bcryptOf('second'));
})->with('account repositories');

it('does not find an account that does not exist', function (AccountRepository $repository): void {
    $repository->get(new UserId(99));
})->with('account repositories')->throws(AccountNotFound::class);

it('does not save an account that does not exist', function (AccountRepository $repository): void {
    $repository->save(new Account(new UserId(99), Name::from('Bia'), new Credentials(EmailAddress::from('bia@example.com'), bcryptOf('first')), Role::Shopper));
})->with('account repositories')->throws(AccountNotFound::class);
