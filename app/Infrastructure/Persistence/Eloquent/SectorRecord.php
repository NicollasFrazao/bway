<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Store\SectorKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

/**
 * A row of `sectors`: a sector of a store, its rectangle on the grid and its kind.
 */
#[Table('sectors')]
#[Fillable(['store_id', 'name', 'kind', 'x', 'y', 'width', 'height'])]
final class SectorRecord extends Model
{
    /**
     * The kind as the domain's SectorKind, stored as `entrance` or `sales`.
     *
     * SectorKind has no backing values, so the domain does not depend on how the column spells them.
     *
     * @return Attribute<SectorKind, SectorKind>
     */
    protected function kind(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): SectorKind => match ($value) {
                'entrance' => SectorKind::Entrance,
                'sales' => SectorKind::Sales,
                default => throw new UnexpectedValueException('A sector kind is stored as "entrance" or "sales"; the row holds another value.'),
            },
            set: static fn (SectorKind $kind): string => match ($kind) {
                SectorKind::Entrance => 'entrance',
                SectorKind::Sales => 'sales',
            },
        );
    }
}
