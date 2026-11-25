<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Application\Account\ResetPassword;
use App\Domain\Account\AccountNotFound;
use App\Domain\Account\InvalidAccountData;
use App\Domain\Shared\UserId;
use App\Http\Requests\Account\ResetPasswordRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

/**
 * The page a reset link opens: the broker checks the token, then the use case stores the new
 * password.
 */
final readonly class NewPasswordController
{
    /**
     * Takes the use case that stores the new password.
     */
    public function __construct(private ResetPassword $reset) {}

    /**
     * The form a link opens.
     */
    public function create(Request $request, string $token): View
    {
        return view('accounts.reset-password', ['token' => $token, 'email' => $request->string('email')]);
    }

    /**
     * Sets the new password when the link is valid.
     *
     * @throws ValidationException when the new password is refused, or the link is invalid or expired.
     * @throws AccountNotFound when the account is gone.
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $password = $request->string('password')->toString();

        try {
            $status = Password::reset(
                [
                    'email' => $request->email(),
                    'token' => $request->string('token')->toString(),
                    'password' => $password,
                ],
                fn (Authenticatable $user) => $this->resetFor($user, $password),
            );
        } catch (InvalidAccountData $error) {
            throw AccountErrors::toValidation($error);
        }

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __('accounts.errors.reset_link')]);
        }

        return redirect()->route('login')->with('status', __('accounts.status.password_reset'));
    }

    /**
     * Stores the new password of the account the link belongs to.
     *
     * @throws InvalidAccountData when the new password is refused.
     * @throws AccountNotFound when the account is gone.
     * @throws UnexpectedValueException when the broker hands over a user without an integer id.
     */
    private function resetFor(Authenticatable $user, string $password): void
    {
        $id = $user->getAuthIdentifier();

        if (! is_int($id)) {
            throw new UnexpectedValueException('The password broker handed over a user without an integer id.');
        }

        $this->reset->handle(new UserId($id), $password);
    }
}
