<?php

declare(strict_types=1);

use App\Domain\Catalog\ProductName;
use App\Domain\Shared\InvalidName;

it('normalises the text as any name', function (): void {
    expect(ProductName::from('  Rice   5 kg ')->value)->toBe('Rice 5 kg');
});

it('refuses what a name refuses', function (): void {
    expect(fn (): ProductName => ProductName::from('   '))->toThrow(InvalidName::class);
});
