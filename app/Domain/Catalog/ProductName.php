<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;

/**
 * The name a product is known by in the catalogue, under the same rules as any other name.
 *
 * A type of its own so a store or sector name cannot be passed where a product name is expected.
 */
final readonly class ProductName
{
    /**
     * Holds an already normalised name; use {@see ProductName::from()} to build one.
     */
    private function __construct(public string $value) {}

    /**
     * Normalises and checks the text as {@see Name::from()} does.
     *
     * @throws InvalidName when the text is not valid UTF-8, is blank, or is too long.
     */
    public static function from(string $text): self
    {
        return new self(Name::from($text)->value);
    }
}
