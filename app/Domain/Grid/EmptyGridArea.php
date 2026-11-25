<?php

declare(strict_types=1);

namespace App\Domain\Grid;

/**
 * A grid area was given a size that covers no cell.
 */
final class EmptyGridArea extends InvalidGridArea
{
    /**
     * Builds the error for the refused size.
     */
    public static function withSize(int $width, int $height): self
    {
        return new self(sprintf('A grid area covers at least one cell; %d x %d covers none.', $width, $height));
    }
}
