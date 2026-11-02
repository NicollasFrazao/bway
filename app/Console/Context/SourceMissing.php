<?php

declare(strict_types=1);

namespace App\Console\Context;

use RuntimeException;

/**
 * The context index the surfaces copy does not exist.
 */
final class SourceMissing extends RuntimeException
{
    /**
     * Builds the error for the index at the given repository-relative path.
     */
    public static function at(string $path): self
    {
        return new self(sprintf('%s is missing: it is the source every agent surface copies.', $path));
    }
}
