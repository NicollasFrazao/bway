<?php

declare(strict_types=1);

namespace App\Infrastructure\FloorPlan;

use App\Domain\FloorPlan\FloorPlanDecoder;
use App\Domain\FloorPlan\FloorPlanImage;
use App\Domain\FloorPlan\UnreadableFloorPlan;
use GdImage;

/**
 * Decodes floor plans with PHP's GD extension: JPEG, PNG, GIF and WebP.
 */
final readonly class GdFloorPlanDecoder implements FloorPlanDecoder
{
    /**
     * The formats GD is asked to decode; anything else is refused before GD sees it.
     */
    private const array FORMATS = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    /**
     * The entry of getimagesizefromstring()'s answer that holds the IMAGETYPE_* constant.
     */
    private const int TYPE = 2;

    /**
     * Decodes the image and turns a palette image into true color, so every pixel reads as RGB.
     *
     * @throws UnreadableFloorPlan when the bytes are not one of the supported formats, or GD fails
     *                             or warns while decoding them (a PNG cut short, say).
     */
    public function decode(string $contents): FloorPlanImage
    {
        $size = $contents === '' ? false : getimagesizefromstring($contents);

        if (
            $size === false
            || ! in_array($size[self::TYPE], self::FORMATS, true)
        ) {
            throw UnreadableFloorPlan::ofBytes(strlen($contents));
        }

        $image = $this->createImage($contents);
        imagepalettetotruecolor($image);

        return new GdFloorPlanImage($image);
    }

    /**
     * Has GD decode the bytes, treating any warning it raises as a failure: a half-decoded plan
     * would map to a wrong grid.
     *
     * @throws UnreadableFloorPlan when GD fails or warns.
     */
    private function createImage(string $contents): GdImage
    {
        $warned = false;
        set_error_handler(static function () use (&$warned): bool {
            $warned = true;

            return true;
        });

        try {
            $image = imagecreatefromstring($contents);
        } finally {
            restore_error_handler();
        }

        if (
            $image === false
            || $warned
        ) {
            throw UnreadableFloorPlan::ofBytes(strlen($contents));
        }

        return $image;
    }
}
