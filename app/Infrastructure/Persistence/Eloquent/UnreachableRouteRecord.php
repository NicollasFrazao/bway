<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `unreachable_routes`: an ordered pair of sectors of a store that no walkway joins.
 */
#[Table('unreachable_routes', timestamps: false)]
#[Fillable(['store_id', 'from_sector_id', 'to_sector_id'])]
final class UnreachableRouteRecord extends Model {}
