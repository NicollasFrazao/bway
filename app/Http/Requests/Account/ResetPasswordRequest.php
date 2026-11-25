<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The form a reset link opens: the token, the email address and the new password twice.
 */
final class ResetPasswordRequest extends FormRequest
{
    /**
     * The longest text the fields accept.
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
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'password' => ['required', 'string', 'max:'.self::MAX_LENGTH, 'confirmed'],
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
