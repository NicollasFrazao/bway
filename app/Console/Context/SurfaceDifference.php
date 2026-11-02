<?php

declare(strict_types=1);

namespace App\Console\Context;

/**
 * The first line where an agent surface stops being a copy of the context index.
 */
final readonly class SurfaceDifference
{
    /**
     * What a missing line is shown as, on either side.
     */
    public const string END_OF_FILE = '(end of file)';

    /**
     * What a surface that does not exist is shown as.
     */
    public const string MISSING_FILE = '(file missing)';

    /**
     * Describes one difference.
     *
     * @param  int  $line  1-based line number of the first difference.
     */
    public function __construct(
        public string $surface,
        public int $line,
        public string $sourceLine,
        public string $surfaceLine,
    ) {}

    /**
     * Finds the first differing line between the index and a surface, comparing bytes without normalising.
     *
     * @param  string|null  $copy  Contents of the surface; null when it does not exist.
     * @return self|null Null when the surface is an exact copy.
     */
    public static function between(string $surface, string $source, ?string $copy): ?self
    {
        if ($source === $copy) {
            return null;
        }

        if ($copy === null) {
            return new self($surface, 1, explode("\n", $source)[0], self::MISSING_FILE);
        }

        $sourceLines = explode("\n", $source);
        $copyLines = explode("\n", $copy);
        $index = 0;

        while (($sourceLines[$index] ?? null) === ($copyLines[$index] ?? null)) {
            $index++;
        }

        return new self(
            surface: $surface,
            line: $index + 1,
            sourceLine: $sourceLines[$index] ?? self::END_OF_FILE,
            surfaceLine: $copyLines[$index] ?? self::END_OF_FILE,
        );
    }
}
