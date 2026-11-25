<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `partitions`: a shelf or divider of a store, its rectangle on the grid.
 */
#[Table('partitions')]
#[Fillable(['store_id', 'x', 'y', 'width', 'height'])]
final class PartitionRecord extends Model {}
