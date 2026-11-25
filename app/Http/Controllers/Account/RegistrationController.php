<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Application\Account\RegisterAccount;
use App\Domain\Account\EmailAlreadyRegistered;
use App\Domain\Account\InvalidAccountData;
use App\Http\Requests\Account\RegisterRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * The sign-up page: registers a shopper and signs them in.
 */
final readonly class RegistrationController
{
    /**
     * Takes the sign-up use case.
     */
    public function __construct(private RegisterAccount $register) {}

    /**
     * The empty form.
     */
    public function create(): View
    {
        return view('accounts.register');
    }

    /**
     * Registers the account, signs it in and opens its shopping list.
     *
     * @throws ValidationException when a value is refused or the email address is taken.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        try {
            $id = $this->register->handle($request->toInput());
        } catch (InvalidAccountData|EmailAlreadyRegistered $error) {
            throw AccountErrors::toValidation($error);
        }

        Auth::loginUsingId($id->value);
        $request->session()->regenerate();

        return redirect()->route('shopping-list');
    }
}
