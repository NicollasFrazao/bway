<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A row of `shopping_lists`, the list of one user, with its items.
 */
#[Table('shopping_lists')]
#[Fillable(['user_id'])]
final class ShoppingListRecord extends Model
{
    /**
     * The items of the list, in the order they were added.
     *
     * @return HasMany<ShoppingListItemRecord, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShoppingListItemRecord::class, 'shopping_list_id')->orderBy('position');
    }
}
