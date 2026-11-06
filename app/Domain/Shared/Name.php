<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * A human-readable name, as people type it: trimmed, single-spaced, never blank.
 */
final readonly class Name
{
    /**
     * The longest name accepted, in characters; the 2020 schema stored names in 100 characters.
     */
    public const int MAX_LENGTH = 100;

    /**
     * Holds an already normalised name; use {@see Name::from()} to build one.
     */
    private function __construct(public string $value) {}

    /**
     * Normalises the text (outer spaces removed, inner runs of whitespace made one space) and checks it.
     *
     * @throws InvalidName when the text is not valid UTF-8, is blank, or is longer than MAX_LENGTH characters.
     */
    public static function from(string $text): self
    {
        $normalised = preg_replace('/\s+/u', ' ', $text);

        if ($normalised === null) {
            throw InvalidName::malformed();
        }

        $normalised = trim($normalised);

        if ($normalised === '') {
            throw InvalidName::blank();
        }

        if (mb_strlen($normalised) > self::MAX_LENGTH) {
            throw InvalidName::tooLong($normalised, self::MAX_LENGTH);
        }

        return new self($normalised);
    }
}
