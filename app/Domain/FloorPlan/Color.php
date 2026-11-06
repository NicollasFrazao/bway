<?php

declare(strict_types=1);

namespace App\Domain\FloorPlan;

/**
 * The color of one pixel of a floor plan, as 8-bit red, green and blue channels.
 */
final readonly class Color
{
    /**
     * The brightest value of a channel.
     */
    public const int MAX_CHANNEL = 255;

    /**
     * Mixes a color.
     *
     * @param  int  $red  0 to 255.
     * @param  int  $green  0 to 255.
     * @param  int  $blue  0 to 255.
     *
     * @throws InvalidColor when a channel is outside 0 to 255.
     */
    public function __construct(
        public int $red,
        public int $green,
        public int $blue,
    ) {
        foreach ([$red, $green, $blue] as $channel) {
            if (
                $channel < 0
                || $channel > self::MAX_CHANNEL
            ) {
                throw InvalidColor::withChannels($red, $green, $blue);
            }
        }
    }
}
