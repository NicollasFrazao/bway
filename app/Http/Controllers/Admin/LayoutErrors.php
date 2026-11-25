<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\FloorPlan\UnreadableFloorPlan;
use App\Domain\FloorPlan\WallFrameNotFound;
use App\Domain\Grid\InvalidGridArea;
use App\Domain\Shared\InvalidName;
use App\Domain\Store\LayoutOutsideGrid;
use App\Domain\Store\LayoutRefused;
use App\Domain\Store\StoreWithoutEntrance;
use Illuminate\Validation\ValidationException;

/**
 * Turns the errors a store form can cause into a validation error on the field they concern.
 */
final class LayoutErrors
{
    /**
     * The validation error for the domain error.
     */
    public static function toValidation(
        InvalidName|InvalidGridArea|LayoutRefused|UnreadableFloorPlan|WallFrameNotFound $error,
    ): ValidationException {
        [$field, $key] = match (true) {
            $error instanceof InvalidName => ['name', 'name'],
            $error instanceof InvalidGridArea => ['area', 'area'],
            $error instanceof LayoutOutsideGrid => ['area', 'outside'],
            $error instanceof StoreWithoutEntrance => ['area', 'entrance'],
            $error instanceof LayoutRefused => ['area', 'overlap'],
            $error instanceof UnreadableFloorPlan => ['floor_plan', 'unreadable'],
            default => ['floor_plan', 'no_frame'],
        };

        return ValidationException::withMessages([$field => __("stores.errors.{$key}")]);
    }
}
