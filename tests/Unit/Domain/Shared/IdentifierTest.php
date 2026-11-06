<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductId;
use App\Domain\Shared\Identifier;
use App\Domain\Shared\InvalidIdentifier;
use App\Domain\Shared\UserId;
use App\Domain\Store\PartitionId;
use App\Domain\Store\SectorId;
use App\Domain\Store\StoreId;

dataset('identifiers', [
    'user' => UserId::class,
    'product' => ProductId::class,
    'store' => StoreId::class,
    'sector' => SectorId::class,
    'partition' => PartitionId::class,
]);

it('wraps a positive key', function (string $kind): void {
    assert(is_subclass_of($kind, Identifier::class));

    $identifier = new $kind(42);

    expect($identifier)->toBeInstanceOf($kind)
        ->and($identifier->value)->toBe(42);
})->with('identifiers');

it('refuses zero and negative keys, naming the kind of identifier', function (string $kind, int $value): void {
    assert(is_subclass_of($kind, Identifier::class));

    expect(fn (): Identifier => new $kind($value))
        ->toThrow(InvalidIdentifier::class, "{$kind} must be a positive integer, {$value} given.");
})->with('identifiers')->with([0, -1]);
