<?php

declare(strict_types=1);

namespace App\Infrastructure\FloorPlan;

use App\Domain\FloorPlan\Color;
use App\Domain\Grid\CellKind;
use App\Domain\Grid\Grid;
use App\Domain\Grid\GridPainter;
use RuntimeException;

/**
 * Draws a grid with GD, one pixel per cell, in the colours of the legend a floor plan is drawn in:
 * red walls, blue sector outlines, green partitions, orange walkways, white floor.
 */
final readonly class GdGridPainter implements GridPainter
{
    /**
     * The hexadecimal digits of one channel of a colour written as "#rrggbb".
     */
    private const int HEX_DIGITS_PER_CHANNEL = 2;

    /**
     * The position of the blue channel among the three.
     */
    private const int BLUE = 2;

    /**
     * White, for the floor.
     */
    private const string FLOOR = '#ffffff';

    /**
     * Red, for the walls.
     */
    private const string WALL = '#dc2626';

    /**
     * Blue, for the sector outlines.
     */
    private const string SECTOR = '#2563eb';

    /**
     * Green, for the partitions.
     */
    private const string PARTITION = '#16a34a';

    /**
     * Orange, for the walkways.
     */
    private const string WALKWAY = '#f97316';

    /**
     * The grid as a PNG image of its size.
     *
     * @throws RuntimeException when GD cannot make the image.
     */
    public function png(Grid $grid): string
    {
        $image = imagecreatetruecolor(max(1, $grid->width), max(1, $grid->height));

        if ($image === false) {
            throw new RuntimeException('GD could not create the image.');
        }

        $colours = [];

        foreach (CellKind::cases() as $kind) {
            [$red, $green, $blue] = $this->rgb($kind);
            $colours[$kind->name] = (int) imagecolorallocate($image, $red, $green, $blue);
        }

        foreach ($grid->rows() as $y => $row) {
            foreach ($row as $x => $kind) {
                imagesetpixel($image, $x, $y, $colours[$kind->name]);
            }
        }

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    /**
     * The colour of a kind of cell, as red, green and blue.
     *
     * @return array{int<0, 255>, int<0, 255>, int<0, 255>}
     */
    private function rgb(CellKind $kind): array
    {
        $hex = match ($kind) {
            CellKind::Floor => self::FLOOR,
            CellKind::Wall => self::WALL,
            CellKind::Sector => self::SECTOR,
            CellKind::Partition => self::PARTITION,
            CellKind::Walkway => self::WALKWAY,
        };
        $channels = array_map(
            static fn (string $pair): int => max(0, min(Color::MAX_CHANNEL, (int) hexdec($pair))),
            str_split(ltrim($hex, '#'), self::HEX_DIGITS_PER_CHANNEL),
        );

        return [$channels[0], $channels[1], $channels[self::BLUE]];
    }
}
