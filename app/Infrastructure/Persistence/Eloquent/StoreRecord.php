<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A row of `stores`, with its sectors and partitions: the domain's Store, as EloquentStoreRepository
 * loads it.
 */
#[Table('stores')]
#[Fillable(['name'])]
final class StoreRecord extends Model
{
    /**
     * The sectors of the store, entrance included, in the order of their ids.
     *
     * @return HasMany<SectorRecord, $this>
     */
    public function sectors(): HasMany
    {
        return $this->hasMany(SectorRecord::class, 'store_id')->orderBy('id');
    }

    /**
     * The partitions of the store, in the order of their ids.
     *
     * @return HasMany<PartitionRecord, $this>
     */
    public function partitions(): HasMany
    {
        return $this->hasMany(PartitionRecord::class, 'store_id')->orderBy('id');
    }
}
