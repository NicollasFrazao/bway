<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

/**
 * A floor plan seen turned a quarter turn counter-clockwise, without copying its pixels.
 *
 * The mapper reads every plan in portrait, as v1 did: a landscape plan is turned so its top-right
 * corner becomes the top-left.
 */
final readonly class QuarterTurnedImage implements FloorPlanImage
{
    /**
     * Wraps the plan to turn.
     */
    public function __construct(private FloorPlanImage $image) {}

    /**
     * The turned plan is as wide as the original is tall.
     */
    public function width(): int
    {
        return $this->image->height();
    }

    /**
     * The turned plan is as tall as the original is wide.
     */
    public function height(): int
    {
        return $this->image->width();
    }

    /**
     * The pixel that the turn brought to (x, y).
     */
    public function colorAt(int $x, int $y): Color
    {
        return $this->image->colorAt($this->image->width() - 1 - $y, $x);
    }
}
