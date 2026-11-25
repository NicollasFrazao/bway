<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The form that asks for a reset link: an email address.
 */
final class ForgotPasswordRequest extends FormRequest
{
    /**
     * The longest text the field accepts.
     */
    private const int MAX_LENGTH = 255;

    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:'.self::MAX_LENGTH],
        ];
    }

    /**
     * The address as the accounts store it: trimmed and lower-cased.
     */
    public function email(): string
    {
        return mb_strtolower(trim($this->string('email')->toString()));
    }
}
