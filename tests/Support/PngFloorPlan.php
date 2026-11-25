<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

/**
 * A floor plan drawn as text and encoded as PNG with GD, as an administrator would upload it: `R` red
 * wall, `B` blue sector outline, `G` green partition, `O` orange walkway, `.` white floor.
 */
final class PngFloorPlan
{
    /**
     * The colour of each letter, as RGB.
     */
    private const array COLOURS = [
        'R' => [255, 0, 0],
        'B' => [0, 0, 255],
        'G' => [0, 255, 0],
        'O' => [255, 102, 0],
        '.' => [255, 255, 255],
    ];

    /**
     * The PNG of the drawing, one pixel per letter.
     *
     * @throws RuntimeException when GD cannot make the image.
     */
    public static function of(string ...$rows): string
    {
        $rows = array_values($rows);
        $image = imagecreatetruecolor(max(1, strlen($rows[0] ?? '')), max(1, count($rows)));

        if ($image === false) {
            throw new RuntimeException('GD could not create the image.');
        }

        foreach ($rows as $y => $row) {
            foreach (str_split($row) as $x => $letter) {
                [$red, $green, $blue] = self::COLOURS[$letter] ?? self::COLOURS['.'];
                imagesetpixel($image, $x, $y, (int) imagecolorallocate($image, $red, $green, $blue));
            }
        }

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    /**
     * A 7 × 7 shop: a wall frame around a U of walkways; cells (1..5, 1) and (1, 2..5), (5, 2..5)
     * walk, the middle is floor, so a sector on (3, 3) cannot be reached.
     */
    public static function uShop(): string
    {
        return self::of('RRRRRRR', 'ROOOOOR', 'RO...OR', 'RO...OR', 'RO...OR', 'ROOOOOR', 'RRRRRRR');
    }
}
