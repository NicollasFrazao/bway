<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

use App\Domain\Grid\CellKind;
use App\Domain\Grid\Grid;
use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;

/**
 * Turns a floor-plan picture into the grid of the store it shows (domain service).
 *
 * The store is the rectangle framed by its outer wall: the frame starts at the first wall pixel
 * (reading rows from the top, each from the left), is as wide as the wall runs to the right from
 * there and as tall as the wall runs down from that run's last pixel. Each pixel inside the frame,
 * the frame included, becomes one cell, of the kind the classifier reads from its color. A
 * landscape plan is read turned to portrait first. This is v1's mapping, without its two slips: v1
 * left the right-hand wall as floor and drew a wall across the row whose index equals the width.
 */
final readonly class FloorPlanMapper
{
    /**
     * Takes the strategy that reads a pixel's color, and the decoder of image files.
     */
    public function __construct(
        private ColorClassifier $classifier,
        private FloorPlanDecoder $decoder,
    ) {}

    /**
     * Decodes an image file and maps it.
     *
     * @param  string  $contents  The whole file, as read from storage or an upload.
     *
     * @throws UnreadableFloorPlan when the bytes are not an image the decoder reads.
     * @throws WallFrameNotFound when no pixel of the plan is a wall.
     */
    public function mapFile(string $contents): Grid
    {
        return $this->map($this->decoder->decode($contents));
    }

    /**
     * Maps the plan to a grid of the size of its wall frame.
     *
     * @throws WallFrameNotFound when no pixel of the plan is a wall.
     */
    public function map(FloorPlanImage $floorPlan): Grid
    {
        $image = $floorPlan->width() > $floorPlan->height() ? new QuarterTurnedImage($floorPlan) : $floorPlan;
        $corner = $this->firstWallPixel($image)
            ?? throw WallFrameNotFound::inImage($floorPlan->width(), $floorPlan->height());

        return Grid::fromRows($this->cellsWithin($image, $this->frame($image, $corner)));
    }

    /**
     * Measures the rectangle the outer wall encloses, from its top-left pixel.
     */
    private function frame(FloorPlanImage $image, GridCoordinate $corner): GridArea
    {
        $width = 1;

        while ($this->isWall($image, $corner->x + $width, $corner->y)) {
            $width++;
        }

        $height = 1;

        while ($this->isWall($image, $corner->x + $width - 1, $corner->y + $height)) {
            $height++;
        }

        return new GridArea($corner, $width, $height);
    }

    /**
     * The first wall pixel, reading rows from the top and each row from the left; null when there is
     * none.
     */
    private function firstWallPixel(FloorPlanImage $image): ?GridCoordinate
    {
        for ($y = 0; $y < $image->height(); $y++) {
            for ($x = 0; $x < $image->width(); $x++) {
                if ($this->isWall($image, $x, $y)) {
                    return new GridCoordinate($x, $y);
                }
            }
        }

        return null;
    }

    /**
     * Tells whether the pixel lies on the plan and is drawn as a wall.
     */
    private function isWall(FloorPlanImage $image, int $x, int $y): bool
    {
        return $x < $image->width()
            && $y < $image->height()
            && $this->classifier->classify($image->colorAt($x, $y)) === CellKind::Wall;
    }

    /**
     * Classifies every pixel inside the frame, the frame included.
     *
     * @return list<list<CellKind>> Rows from the top of the frame, each from its left.
     */
    private function cellsWithin(FloorPlanImage $image, GridArea $frame): array
    {
        $rows = [];

        for ($y = $frame->origin->y; $y < $frame->origin->y + $frame->height; $y++) {
            $row = [];

            for ($x = $frame->origin->x; $x < $frame->origin->x + $frame->width; $x++) {
                $row[] = $this->classifier->classify($image->colorAt($x, $y));
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
