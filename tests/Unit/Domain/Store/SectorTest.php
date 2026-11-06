<?php

declare(strict_types=1);

use App\Domain\Grid\GridArea;
use App\Domain\Grid\GridCoordinate;
use App\Domain\Shared\Name;
use App\Domain\Store\Partition;
use App\Domain\Store\PartitionId;
use App\Domain\Store\Sector;
use App\Domain\Store\SectorId;
use App\Domain\Store\SectorKind;
use Tests\Support\StoreLayout;

it('keeps the name and the area of a sector', function (): void {
    $area = new GridArea(new GridCoordinate(10, 20), 4, 3);

    $sector = new Sector(new SectorId(5), Name::from('Bakery'), $area, SectorKind::Sales);

    expect($sector->id->value)->toBe(5)
        ->and($sector->name->value)->toBe('Bakery')
        ->and($sector->area)->toBe($area);
});

it('tells the entrance from a sales sector', function (): void {
    expect(StoreLayout::entrance(1)->isEntrance())->toBeTrue()
        ->and(StoreLayout::sector(2)->isEntrance())->toBeFalse();
});

it('keeps the footprint of a partition', function (): void {
    $area = new GridArea(new GridCoordinate(1, 2), 1, 8);

    $partition = new Partition(new PartitionId(3), $area);

    expect($partition->id->value)->toBe(3)
        ->and($partition->area)->toBe($area);
});
