<?php

declare(strict_types=1);

use App\Domain\Account\Account;
use App\Domain\Account\AccountField;
use App\Domain\Account\Credentials;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\HashedPassword;
use App\Domain\Account\InvalidAccountData;
use App\Domain\Account\PlainPassword;
use App\Domain\Account\Role;
use App\Domain\Shared\Name;
use App\Domain\Shared\UserId;

/**
 * Ana's account with the role given.
 */
function anaWithRole(Role $role): Account
{
    return new Account(new UserId(1), Name::from('Ana'), new Credentials(EmailAddress::from('ana@example.com'), new HashedPassword('h1')), $role);
}

it('reads an email address trimmed and lower-cased', function (): void {
    expect(EmailAddress::from('  Ana.Souza@Example.COM ')->value)->toBe('ana.souza@example.com');
});

it('refuses what is not an email address, naming the field', function (string $text): void {
    try {
        EmailAddress::from($text);
    } catch (InvalidAccountData $error) {
        expect($error->field)->toBe(AccountField::Email)
            ->and($error->getMessage())->toBe('Invalid email: not an email address.');

        return;
    }

    throw new LogicException("{$text} was accepted.");
})->with([
    'no at sign' => 'ana.example.com',
    'blank' => '   ',
    'longer than 254 characters' => str_repeat('a', 64).'@'.str_repeat('b', 63).'.'.str_repeat('c', 63).'.'.str_repeat('d', 61).'.com',
]);

it('accepts a password from 8 characters to 72 bytes', function (string $text): void {
    expect(PlainPassword::from($text)->value)->toBe($text);
})->with([
    'eight characters' => '12345678',
    'eight characters of two bytes' => 'çççççççç',
    'seventy-two bytes' => str_repeat('a', 72),
]);

it('refuses a password shorter than 8 characters or longer than 72 bytes', function (string $text): void {
    try {
        PlainPassword::from($text);
    } catch (InvalidAccountData $error) {
        expect($error->field)->toBe(AccountField::Password);

        return;
    }

    throw new LogicException('The password was accepted.');
})->with([
    'seven characters' => '1234567',
    'seventy-three bytes' => str_repeat('a', 73),
    'thirty-seven characters of two bytes' => str_repeat('ç', 37),
]);

it('lets only an administrator manage the stores', function (): void {
    expect(anaWithRole(Role::Administrator)->canManageStores())->toBeTrue()
        ->and(anaWithRole(Role::Shopper)->canManageStores())->toBeFalse();
});

it('changes the password and keeps the rest', function (): void {
    $account = anaWithRole(Role::Administrator);

    $changed = $account->withPassword(new HashedPassword('h2'));

    expect($changed->credentials->password->value)->toBe('h2')
        ->and($changed->withPassword(new HashedPassword('h1')))->toEqual($account)
        ->and($account->credentials->password->value)->toBe('h1');
});
