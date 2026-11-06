<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `store_grids`: the grid a store's floor plan was mapped to, as its size and its cell codes.
 */
#[Table('store_grids')]
#[Fillable(['store_id', 'width', 'height', 'cells'])]
final class StoreGridRecord extends Model {}
