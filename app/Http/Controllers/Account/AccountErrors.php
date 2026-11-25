<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Domain\Account\AccountField;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\InvalidAccountData;
use Illuminate\Validation\ValidationException;

/**
 * Turns the account errors a form can cause into a validation error on the field they concern.
 */
final class AccountErrors
{
    /**
     * The validation error for a refused value or a taken email address.
     */
    public static function toValidation(InvalidAccountData|EmailAlreadyRegistered $error): ValidationException
    {
        if ($error instanceof EmailAlreadyRegistered) {
            return ValidationException::withMessages([
                AccountField::Email->value => __('accounts.errors.taken'),
            ]);
        }

        return ValidationException::withMessages([
            $error->field->value => __("accounts.errors.invalid.{$error->field->value}"),
        ]);
    }
}
