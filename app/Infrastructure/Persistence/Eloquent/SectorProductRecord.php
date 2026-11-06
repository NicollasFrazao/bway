<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `sector_products`: a store sells a product in one of its sectors.
 */
#[Table('sector_products')]
#[Fillable(['store_id', 'sector_id', 'product_id'])]
final class SectorProductRecord extends Model {}
