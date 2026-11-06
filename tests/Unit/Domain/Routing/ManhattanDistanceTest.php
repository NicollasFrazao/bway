<?php

declare(strict_types=1);

use App\Domain\Grid\GridCoordinate;
use App\Domain\Routing\ManhattanDistance;

it('counts the columns plus the rows between two cells', function (int $fromX, int $fromY, int $toX, int $toY, int $distance): void {
    $estimate = new ManhattanDistance()->estimate(new GridCoordinate($fromX, $fromY), new GridCoordinate($toX, $toY));

    expect($estimate)->toBe($distance);
})->with([
    'the same cell' => [3, 3, 3, 3, 0],
    'along a row' => [1, 4, 6, 4, 5],
    'down and left' => [7, 1, 2, 5, 9],
    'up and right' => [2, 5, 7, 1, 9],
]);
