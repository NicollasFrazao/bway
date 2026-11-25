<?php

declare(strict_types=1);

use App\Domain\Account\Credentials;
use App\Domain\Account\EmailAddress;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\HashedPassword;
use App\Domain\Account\NewAccount;
use App\Domain\Shared\Name;
use App\Domain\Shared\UserId;
use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Persistence\EloquentAccountRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps the remember-me token when the password does not change', function (): void {
    $user = User::factory()->create();
    $repository = new EloquentAccountRepository;

    $repository->save($repository->get(new UserId($user->id)));

    expect($user->refresh()->getRememberToken())->toBe($user->getRememberToken());
});

it('turns a sign-up that raced another one with the same email into EmailAlreadyRegistered', function (): void {
    User::factory()->create(['email' => 'ana@example.com']);

    (new EloquentAccountRepository)->register(
        new NewAccount(Name::from('Ana'), new Credentials(EmailAddress::from('ana@example.com'), new HashedPassword('h'))),
    );
})->throws(EmailAlreadyRegistered::class);

it('refuses a row with a value no sign-up writes', function (string $column, string $value): void {
    $user = User::factory()->create([$column => $value]);

    (new EloquentAccountRepository)->get(new UserId($user->id));
})->with([
    'unknown role' => ['role', 'owner'],
    'malformed email' => ['email', 'not an email'],
    'blank name' => ['name', ' '],
])->throws(UnexpectedValueException::class);
