<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

/**
 * Turns the bytes of an image file into a floor plan the mapper can read (port; implemented in
 * Infrastructure).
 */
interface FloorPlanDecoder
{
    /**
     * Decodes a JPEG, PNG or any other format the adapter supports.
     *
     * @param  string  $contents  The whole file, as read from storage or an upload.
     *
     * @throws UnreadableFloorPlan when the bytes are not an image the adapter can decode.
     */
    public function decode(string $contents): FloorPlanImage;
}
