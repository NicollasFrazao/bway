<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Application\Account\SignIn;
use App\Domain\Account\InvalidCredentials;
use App\Http\Requests\Account\SignInRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Signing in and out. Failed sign-ins are limited per email and address; v1 had no limit.
 */
final readonly class SessionController
{
    /**
     * Failed attempts allowed per email and address before the sign-in waits.
     */
    private const int MAX_ATTEMPTS = 5;

    /**
     * How long, in seconds, failed attempts are counted.
     */
    private const int DECAY_SECONDS = 60;

    /**
     * Takes the sign-in use case.
     */
    public function __construct(private SignIn $signIn) {}

    /**
     * The sign-in form.
     */
    public function create(): View
    {
        return view('accounts.login');
    }

    /**
     * Signs in, renews the session id and opens the page the guest was sent away from.
     *
     * @throws ValidationException when the attempts are used up or the email or password is wrong.
     */
    public function store(SignInRequest $request): RedirectResponse
    {
        $key = $request->throttleKey();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => __('accounts.errors.throttled', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        try {
            $id = $this->signIn->handle(
                $request->string('email')->toString(),
                $request->string('password')->toString(),
            );
        } catch (InvalidCredentials) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => __('accounts.errors.credentials')]);
        }

        RateLimiter::clear($key);
        Auth::loginUsingId($id->value, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('shopping-list'));
    }

    /**
     * Signs out, ends the session and renews the form token.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
