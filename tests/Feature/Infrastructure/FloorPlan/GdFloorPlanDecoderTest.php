<?php

declare(strict_types=1);

use App\Domain\FloorPlan\Color;
use App\Domain\FloorPlan\FloorPlanImage;
use App\Domain\FloorPlan\UnreadableFloorPlan;
use App\Infrastructure\FloorPlan\GdFloorPlanDecoder;

/**
 * A 2 x 1 image, red then orange, encoded by GD in the given format.
 *
 * @param  callable(GdImage): bool  $encode  imagepng, imagegif...
 */
function encodedPlan(callable $encode, bool $palette = false): string
{
    $image = $palette ? imagecreate(2, 1) : imagecreatetruecolor(2, 1);

    if ($image === false) {
        throw new RuntimeException('GD could not create the image.');
    }

    imagesetpixel($image, 0, 0, (int) imagecolorallocate($image, 255, 0, 0));
    imagesetpixel($image, 1, 0, (int) imagecolorallocate($image, 255, 102, 0));
    ob_start();
    $encode($image);

    return (string) ob_get_clean();
}

it('reads the pixels of a decoded image as colors', function (string $contents): void {
    $plan = new GdFloorPlanDecoder()->decode($contents);

    expect([$plan->width(), $plan->height()])->toBe([2, 1])
        ->and($plan->colorAt(0, 0))->toEqual(new Color(255, 0, 0))
        ->and($plan->colorAt(1, 0))->toEqual(new Color(255, 102, 0));
})->with([
    'png' => fn (): string => encodedPlan(imagepng(...)),
    'palette png' => fn (): string => encodedPlan(imagepng(...), palette: true),
    'gif' => fn (): string => encodedPlan(imagegif(...), palette: true),
]);

it('decodes the JPEG floor plan v1 mapped', function (): void {
    $plan = new GdFloorPlanDecoder()->decode((string) file_get_contents(__DIR__.'/../../../Fixtures/v1/store-1/floor-plan.jpg'));

    expect([$plan->width(), $plan->height()])->toBe([480, 640]);
});

it('refuses bytes that are not an image it decodes', function (string $contents): void {
    expect(fn (): FloorPlanImage => new GdFloorPlanDecoder()->decode($contents))
        ->toThrow(UnreadableFloorPlan::class, sprintf('The floor plan (%d bytes) is not an image that can be decoded.', strlen($contents)));
})->with([
    'nothing' => [''],
    'text' => ['not an image'],
    'a format GD is not asked to decode' => [base64_decode('Qk0eAAAAAAAAABoAAAAMAAAAAQABAAEAGAAAAP8A', true)],
    'a PNG cut short after its header' => fn (): string => substr(encodedPlan(imagepng(...)), 0, 40),
]);
