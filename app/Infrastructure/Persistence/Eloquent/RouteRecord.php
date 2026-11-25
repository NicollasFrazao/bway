<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `routes`: the route kept for an ordered pair of sectors of a store, as JSON cells.
 */
#[Table('routes')]
#[Fillable(['store_id', 'from_sector_id', 'to_sector_id', 'cells'])]
final class RouteRecord extends Model {}
