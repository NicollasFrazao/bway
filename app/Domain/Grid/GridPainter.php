<?php

declare(strict_types=1);

namespace App\Domain\Grid;

/**
 * Draws a mapped grid as a picture, one pixel per cell in the colours of the legend, so whoever lays
 * out a store sees where its walls, walkways and shelves are (port; implemented in Infrastructure).
 */
interface GridPainter
{
    /**
     * The grid as a PNG image.
     */
    public function png(Grid $grid): string;
}
