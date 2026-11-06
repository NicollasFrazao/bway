<?php

declare(strict_types=1);

use App\Domain\FloorPlan\Color;
use App\Domain\FloorPlan\InvalidColor;

it('keeps its three channels, from 0 to 255', function (): void {
    $color = new Color(0, 128, 255);

    expect([$color->red, $color->green, $color->blue])->toBe([0, 128, 255]);
});

it('refuses a channel outside 0 to 255', function (int $red, int $green, int $blue): void {
    expect(fn (): Color => new Color($red, $green, $blue))
        ->toThrow(InvalidColor::class, "Color channels run from 0 to 255; got ({$red}, {$green}, {$blue}).");
})->with([
    'red too high' => [256, 0, 0],
    'green negative' => [0, -1, 0],
    'blue too high' => [0, 0, 300],
]);
