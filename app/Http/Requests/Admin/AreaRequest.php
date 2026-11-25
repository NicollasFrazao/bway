<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Application\Store\AreaInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A form with a rectangle on the grid (x, y, width, height, in cells) and, for a sector, a name.
 */
final class AreaRequest extends FormRequest
{
    /**
     * The longest name the field accepts; the domain allows 100 characters.
     */
    private const int MAX_LENGTH = 255;

    /**
     * The largest number a coordinate field accepts.
     */
    private const int MAX_CELLS = 100_000;

    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $number = ['required', 'integer', 'min:0', 'max:'.self::MAX_CELLS];

        return [
            'name' => ['sometimes', 'string', 'max:'.self::MAX_LENGTH],
            'x' => $number,
            'y' => $number,
            'width' => $number,
            'height' => $number,
        ];
    }

    /**
     * The rectangle typed.
     */
    public function area(): AreaInput
    {
        return new AreaInput($this->integer('x'), $this->integer('y'), $this->integer('width'), $this->integer('height'));
    }
}
