<?php

declare(strict_types=1);

use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\FloorPlan\WallFrameNotFound;
use App\Domain\Grid\Grid;
use Tests\Support\DrawnFloorPlan;
use Tests\Support\GridDrawing;
use Tests\Support\TextFloorPlanDecoder;

/**
 * The mapper with v1's legend, reading text files.
 */
function textMapper(): FloorPlanMapper
{
    return new FloorPlanMapper(new LegendColorClassifier, new TextFloorPlanDecoder);
}

/**
 * Maps a drawn floor plan with v1's legend.
 *
 * @param  list<string>  $picture  Rows of DrawnFloorPlan's legend.
 */
function mapPlan(array $picture): Grid
{
    return textMapper()->map(new DrawnFloorPlan($picture));
}

it('maps the pixels inside the red wall frame to cells, the frame included', function (): void {
    $grid = mapPlan([
        '........',
        '..RRRRR.',
        '..ROOBR.',
        '..RG..R.',
        '..RRRRR.',
        '.......B',
        '........',
        '........',
    ]);

    expect($grid)->toEqual(GridDrawing::grid([
        '#####',
        '#ooS#',
        '#P..#',
        '#####',
    ]));
});

it('takes the frame from the first wall pixel, as wide as the wall runs right and as tall as it runs down at the end of that run', function (): void {
    $grid = mapPlan([
        'RRRR.',
        'R..R.',
        'R..RR',
        'RRRRR',
        '...R.',
    ]);

    expect($grid)->toEqual(GridDrawing::grid([
        '####',
        '#..#',
        '#..#',
        '####',
        '...#',
    ]));
});

it('maps a frame that reaches the edges of the picture', function (): void {
    expect(mapPlan(['RR', 'RR']))->toEqual(GridDrawing::grid(['##', '##']));
});

it('turns a landscape plan to portrait before mapping it, as v1 did', function (): void {
    $grid = mapPlan([
        'RRRR',
        'RO.R',
        'RRRR',
    ]);

    expect($grid)->toEqual(GridDrawing::grid([
        '###',
        '#.#',
        '#o#',
        '###',
    ]));
});

it('decodes an image file before mapping it', function (): void {
    expect(textMapper()->mapFile("RRR\nROR\nRRR"))->toEqual(GridDrawing::grid(['###', '#o#', '###']));
});

it('refuses a plan without a wall', function (): void {
    expect(fn (): Grid => mapPlan(['.OB', 'G..']))
        ->toThrow(WallFrameNotFound::class, 'The 3 x 2 floor plan has no wall pixel; the outline of the store must be drawn as walls.')
        ->and(fn (): Grid => mapPlan(['.O', 'B.', 'G.']))
        ->toThrow(WallFrameNotFound::class, 'The 2 x 3 floor plan has no wall pixel;');
});
