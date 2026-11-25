<?php

declare(strict_types=1);

use App\Domain\Account\PasswordHasher;
use App\Domain\Account\PlainPassword;
use App\Infrastructure\Account\FrameworkPasswordHasher;
use Illuminate\Contracts\Hashing\Hasher;
use Mockery\MockInterface;

it('hashes with bcrypt and checks the typed password against the hash', function (): void {
    $hasher = app(PasswordHasher::class);

    $hash = $hasher->hash(PlainPassword::from('correct horse'));

    expect($hash->value)->toStartWith('$2y$')
        ->and($hasher->matches('correct horse', $hash))->toBeTrue()
        ->and($hasher->matches('wrong horse', $hash))->toBeFalse();
});

it('spends one hash check on an unknown email, hashing the decoy once per process', function (): void {
    /**
     * The framework's hasher, recording the calls it gets.
     *
     * @var Hasher&MockInterface $inner
     */
    $inner = Mockery::spy(Hasher::class);
    $inner->allows('make')->andReturn('decoy hash');
    $hasher = new FrameworkPasswordHasher($inner);

    $hasher->spendCheck('anything');
    $hasher->spendCheck('anything else');

    $inner->shouldHaveReceived('make')->once();
    $inner->shouldHaveReceived('check')->with('anything', 'decoy hash')->once();
    $inner->shouldHaveReceived('check')->with('anything else', 'decoy hash')->once();
});
