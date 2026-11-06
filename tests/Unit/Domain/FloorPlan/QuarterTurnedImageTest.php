<?php

declare(strict_types=1);

use App\Domain\FloorPlan\Color;
use App\Domain\FloorPlan\QuarterTurnedImage;
use Tests\Support\DrawnFloorPlan;

it('turns a picture a quarter turn counter-clockwise', function (): void {
    $turned = new QuarterTurnedImage(new DrawnFloorPlan([
        'RGB',
        'O..',
    ]));

    $pixels = [];

    for ($y = 0; $y < $turned->height(); $y++) {
        for ($x = 0; $x < $turned->width(); $x++) {
            $pixels[$y][$x] = $turned->colorAt($x, $y);
        }
    }

    expect([$turned->width(), $turned->height()])->toBe([2, 3])
        ->and($pixels)->toEqual([
            [new Color(0, 0, 255), new Color(255, 255, 255)],
            [new Color(0, 255, 0), new Color(255, 255, 255)],
            [new Color(255, 0, 0), new Color(255, 102, 0)],
        ]);
});
