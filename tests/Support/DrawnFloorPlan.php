<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\FloorPlan\Color;
use App\Domain\FloorPlan\FloorPlanImage;
use LogicException;

/**
 * A floor-plan picture drawn as text, one string per row of pixels: `R` red, `B` blue, `G` green,
 * `O` orange (255, 102, 0, the walkways of v1's plans), `.` white.
 */
final readonly class DrawnFloorPlan implements FloorPlanImage
{
    /**
     * The color of each character, as red, green and blue.
     */
    private const array PALETTE = [
        'R' => [255, 0, 0],
        'B' => [0, 0, 255],
        'G' => [0, 255, 0],
        'O' => [255, 102, 0],
        '.' => [255, 255, 255],
    ];

    /**
     * Holds the picture.
     *
     * @param  list<string>  $rows  All of the same length.
     */
    public function __construct(private array $rows) {}

    /**
     * Characters per row.
     */
    public function width(): int
    {
        return strlen($this->rows[0] ?? '');
    }

    /**
     * Rows of the picture.
     */
    public function height(): int
    {
        return count($this->rows);
    }

    /**
     * The color drawn at (x, y).
     *
     * @throws LogicException when the character there is not in the palette.
     */
    public function colorAt(int $x, int $y): Color
    {
        $character = $this->rows[$y][$x] ?? '';
        [$red, $green, $blue] = self::PALETTE[$character] ?? throw new LogicException("The palette has no '{$character}'.");

        return new Color($red, $green, $blue);
    }
}
