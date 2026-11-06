<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\FloorPlan\FloorPlanDecoder;
use App\Domain\FloorPlan\FloorPlanImage;

/**
 * Decodes a floor plan drawn as text, rows separated by new lines, with the legend of
 * DrawnFloorPlan; the domain and application tests use it in place of GD.
 */
final readonly class TextFloorPlanDecoder implements FloorPlanDecoder
{
    /**
     * The picture the text draws.
     */
    public function decode(string $contents): FloorPlanImage
    {
        return new DrawnFloorPlan(explode("\n", $contents));
    }
}
