<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

use App\Domain\Grid\CellKind;

/**
 * Reads the legend v1's floor plans were drawn with: red walls, blue sector outlines, green
 * partitions, orange walkways, anything else free floor.
 *
 * The thresholds are v1's, so a plan exported with slightly off colors (JPEG noise) maps the same.
 */
final readonly class LegendColorClassifier implements ColorClassifier
{
    /**
     * A channel at or above this is fully on.
     */
    private const int FULL = 250;

    /**
     * A channel at or below this is off.
     */
    private const int OFF = 10;

    /**
     * The lowest green of the orange walkways.
     */
    private const int ORANGE_GREEN_FROM = 90;

    /**
     * The highest green of the orange walkways.
     */
    private const int ORANGE_GREEN_TO = 110;

    /**
     * The kind of cell drawn in this color.
     */
    public function classify(Color $color): CellKind
    {
        return match (true) {
            $this->isPrimary($color->red, $color->green, $color->blue) => CellKind::Wall,
            $this->isPrimary($color->blue, $color->red, $color->green) => CellKind::Sector,
            $this->isPrimary($color->green, $color->red, $color->blue) => CellKind::Partition,
            $this->isOrange($color) => CellKind::Walkway,
            default => CellKind::Floor,
        };
    }

    /**
     * Tells whether one channel is fully on and the other two are off.
     */
    private function isPrimary(int $on, int $firstOff, int $secondOff): bool
    {
        return $on >= self::FULL
            && $firstOff <= self::OFF
            && $secondOff <= self::OFF;
    }

    /**
     * Tells whether the color is v1's walkway orange: red on, green about 100, blue off.
     */
    private function isOrange(Color $color): bool
    {
        return $color->red >= self::FULL
            && $color->green >= self::ORANGE_GREEN_FROM
            && $color->green <= self::ORANGE_GREEN_TO
            && $color->blue <= self::OFF;
    }
}
