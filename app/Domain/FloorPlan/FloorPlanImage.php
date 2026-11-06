<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

/**
 * A decoded floor-plan picture the mapper reads pixel by pixel.
 *
 * Pixels are addressed like grid cells: x counts columns from the left, y rows from the top, both
 * from 0.
 */
interface FloorPlanImage
{
    /**
     * Pixels per row, at least 1.
     */
    public function width(): int;

    /**
     * Rows of pixels, at least 1.
     */
    public function height(): int;

    /**
     * The color of one pixel; the caller keeps x below width() and y below height().
     */
    public function colorAt(int $x, int $y): Color;
}
