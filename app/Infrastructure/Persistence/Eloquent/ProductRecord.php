<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `products`: one product of the catalogue, mapped to and from the domain's Product by
 * EloquentProductCatalog.
 */
#[Table('products')]
#[Fillable(['name'])]
final class ProductRecord extends Model {}
