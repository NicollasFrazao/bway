<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The assortment form: for each product, the sector that sells it, or nothing.
 */
final class AssortmentRequest extends FormRequest
{
    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'sectors' => ['array'],
            'sectors.*' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * The sector of each product placed, keyed by product id; products left empty are not sold.
     *
     * @return array<int, int>
     */
    public function placements(): array
    {
        $placements = [];

        foreach ($this->array('sectors') as $productId => $sectorId) {
            if (
                ! is_numeric($sectorId)
                || (int) $productId < 1
            ) {
                continue;
            }

            $placements[(int) $productId] = (int) $sectorId;
        }

        return $placements;
    }
}
