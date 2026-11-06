<?php

declare(strict_types=1);

namespace App\Infrastructure\FloorPlan;

use App\Domain\FloorPlan\Color;
use App\Domain\FloorPlan\FloorPlanImage;
use GdImage;

/**
 * A floor plan held in memory by GD, read pixel by pixel.
 */
final readonly class GdFloorPlanImage implements FloorPlanImage
{
    /**
     * Where the red channel starts in a GD true-color pixel.
     */
    private const int RED_SHIFT = 16;

    /**
     * Where the green channel starts in a GD true-color pixel.
     */
    private const int GREEN_SHIFT = 8;

    /**
     * Keeps one 8-bit channel of a shifted pixel.
     */
    private const int CHANNEL_MASK = 0xFF;

    /**
     * Wraps a true-color image.
     */
    public function __construct(private GdImage $image) {}

    /**
     * Pixels per row.
     */
    public function width(): int
    {
        return imagesx($this->image);
    }

    /**
     * Rows of pixels.
     */
    public function height(): int
    {
        return imagesy($this->image);
    }

    /**
     * The color of one pixel; the alpha channel is ignored, as v1 did.
     */
    public function colorAt(int $x, int $y): Color
    {
        $pixel = (int) imagecolorat($this->image, $x, $y);

        return new Color(
            ($pixel >> self::RED_SHIFT) & self::CHANNEL_MASK,
            ($pixel >> self::GREEN_SHIFT) & self::CHANNEL_MASK,
            $pixel & self::CHANNEL_MASK,
        );
    }
}
