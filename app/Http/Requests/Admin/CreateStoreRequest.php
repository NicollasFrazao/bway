<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Application\Store\AreaInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use UnexpectedValueException;

/**
 * The new-store form: a name, the floor plan image and the entrance's rectangle, in cells.
 */
final class CreateStoreRequest extends FormRequest
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
     * The largest floor plan accepted, in kilobytes.
     */
    private const int MAX_PLAN_KILOBYTES = 10_240;

    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $number = ['required', 'integer', 'min:0', 'max:'.self::MAX_CELLS];

        return [
            'name' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'floor_plan' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:'.self::MAX_PLAN_KILOBYTES],
            'x' => $number,
            'y' => $number,
            'width' => $number,
            'height' => $number,
        ];
    }

    /**
     * The entrance's rectangle.
     */
    public function entrance(): AreaInput
    {
        return new AreaInput($this->integer('x'), $this->integer('y'), $this->integer('width'), $this->integer('height'));
    }

    /**
     * The bytes of the uploaded plan.
     *
     * @throws UnexpectedValueException when the validated file is missing.
     */
    public function floorPlan(): string
    {
        $file = $this->file('floor_plan');

        return $file instanceof UploadedFile ? $file->getContent() : throw new UnexpectedValueException('No floor plan was uploaded.');
    }
}
