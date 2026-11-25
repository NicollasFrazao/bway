<?php

declare(strict_types=1);

namespace App\Http\Requests\ShoppingList;

use App\Domain\Catalog\ProductId;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The product to add to the signed-in user's list. Only the product is read: a `user_id` sent with
 * it is ignored, since the list is always the session's.
 */
final class AddProductRequest extends FormRequest
{
    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * The product chosen.
     */
    public function productId(): ProductId
    {
        return new ProductId($this->integer('product_id'));
    }
}
