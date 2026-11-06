<?php

declare(strict_types=1);

use App\Domain\Store\SectorKind;
use App\Infrastructure\Persistence\Eloquent\SectorRecord;

it('stores each kind under its own word and reads it back', function (SectorKind $kind, string $stored): void {
    $record = new SectorRecord(['kind' => $kind]);

    expect($record->getAttributes()['kind'] ?? null)->toBe($stored)
        ->and($record->kind)->toBe($kind);
})->with([
    'entrance' => [SectorKind::Entrance, 'entrance'],
    'sales' => [SectorKind::Sales, 'sales'],
]);

it('refuses a stored kind it does not write', function (): void {
    $record = new SectorRecord()->setRawAttributes(['kind' => 'exit']);

    expect(fn (): SectorKind => $record->kind)
        ->toThrow(UnexpectedValueException::class, 'A sector kind is stored as "entrance" or "sales"; the row holds another value.');
});

it('refuses to store anything but a sector kind', function (): void {
    expect(fn (): SectorRecord => new SectorRecord(['kind' => 'entrance']))->toThrow(TypeError::class);
});
