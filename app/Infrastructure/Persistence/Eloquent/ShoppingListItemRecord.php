<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `shopping_list_items`: one product on a list, at its position, with the name it had when
 * it was added.
 */
#[Table('shopping_list_items')]
#[Fillable(['shopping_list_id', 'position', 'product_id', 'product_name'])]
final class ShoppingListItemRecord extends Model {}
