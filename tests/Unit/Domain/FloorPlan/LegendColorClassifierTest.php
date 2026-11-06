<?php

declare(strict_types=1);

use App\Domain\FloorPlan\Color;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Grid\CellKind;

it('reads the legend of v1 floor plans, with v1 thresholds', function (int $red, int $green, int $blue, CellKind $kind): void {
    expect(new LegendColorClassifier()->classify(new Color($red, $green, $blue)))->toBe($kind);
})->with([
    'pure red is a wall' => [255, 0, 0, CellKind::Wall],
    'red at the thresholds is a wall' => [250, 10, 10, CellKind::Wall],
    'red too dark is floor' => [249, 0, 0, CellKind::Floor],
    'red with too much green is floor' => [255, 11, 0, CellKind::Floor],
    'pure blue is a sector' => [0, 0, 255, CellKind::Sector],
    'blue at the thresholds is a sector' => [10, 10, 250, CellKind::Sector],
    'blue with too much red is floor' => [11, 0, 255, CellKind::Floor],
    'pure green is a partition' => [0, 255, 0, CellKind::Partition],
    'green at the thresholds is a partition' => [10, 250, 10, CellKind::Partition],
    'green with too much blue is floor' => [0, 255, 11, CellKind::Floor],
    'v1 orange is a walkway' => [255, 102, 0, CellKind::Walkway],
    'orange at its lowest green' => [250, 90, 10, CellKind::Walkway],
    'orange at its highest green' => [250, 110, 10, CellKind::Walkway],
    'orange with too little green is floor' => [255, 89, 0, CellKind::Floor],
    'orange with too much green is floor' => [255, 111, 0, CellKind::Floor],
    'orange with too much blue is floor' => [255, 100, 11, CellKind::Floor],
    'white is floor' => [255, 255, 255, CellKind::Floor],
    'black is floor' => [0, 0, 0, CellKind::Floor],
]);
