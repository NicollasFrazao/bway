<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use UnexpectedValueException;

/**
 * A new floor plan for a store.
 */
final class FloorPlanRequest extends FormRequest
{
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
        return ['floor_plan' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:'.self::MAX_PLAN_KILOBYTES]];
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
