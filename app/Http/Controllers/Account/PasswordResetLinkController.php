<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Requests\Account\ForgotPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

/**
 * Asking for a password reset link, sent by the framework's password broker (tokens hashed and
 * expiring after an hour). v1 had no password recovery.
 */
final readonly class PasswordResetLinkController
{
    /**
     * The form that asks for a link.
     */
    public function create(): View
    {
        return view('accounts.forgot-password');
    }

    /**
     * Sends a link when an account has the address; the answer is the same either way, so the form
     * does not tell which emails have an account.
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::sendResetLink(['email' => $request->email()]);

        return back()->with('status', __('accounts.status.reset_link'));
    }
}
