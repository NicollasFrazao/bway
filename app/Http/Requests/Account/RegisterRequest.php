<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Application\Account\RegistrationInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The sign-up form: a name, an email address and the password twice. The domain checks the values;
 * this request checks only their shape.
 */
final class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'email' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'password' => ['required', 'string', 'max:'.self::MAX_LENGTH, 'confirmed'],
        ];
    }

    /**
     * The input for the sign-up use case.
     */
    public function toInput(): RegistrationInput
    {
        return new RegistrationInput(
            $this->string('name')->toString(),
            $this->string('email')->toString(),
            $this->string('password')->toString(),
        );
    }
}
