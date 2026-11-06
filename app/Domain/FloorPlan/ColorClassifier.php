<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

use App\Domain\Grid\CellKind;

/**
 * Decides which kind of cell a pixel of a floor plan stands for (the mapper's strategy).
 */
interface ColorClassifier
{
    /**
     * The kind of cell drawn in this color.
     */
    public function classify(Color $color): CellKind;
}
