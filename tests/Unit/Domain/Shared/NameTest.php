<?php

declare(strict_types=1);

use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;

it('trims the text and collapses inner whitespace into one space', function (): void {
    expect(Name::from("  Fruits \t and\n vegetables ")->value)->toBe('Fruits and vegetables');
});

it('accepts a name of exactly the maximum length, counted in characters', function (): void {
    $name = str_repeat('ç', Name::MAX_LENGTH);

    expect(Name::from($name)->value)->toBe($name);
});

it('refuses a blank name', function (string $text): void {
    expect(fn (): Name => Name::from($text))->toThrow(InvalidName::class, 'A name cannot be blank.');
})->with(['empty' => '', 'whitespace only' => " \t\n "]);

it('refuses a name over the maximum length, quoting its beginning', function (): void {
    $text = str_repeat('a', Name::MAX_LENGTH + 1);

    expect(fn (): Name => Name::from($text))
        ->toThrow(InvalidName::class, 'A name has at most 100 characters; "aaaaaaaaaaaaaaaaaaaa..." has 101.');
});

it('refuses bytes that are not UTF-8', function (): void {
    expect(fn (): Name => Name::from("Caf\xE9"))->toThrow(InvalidName::class, 'A name must be valid UTF-8 text.');
});
