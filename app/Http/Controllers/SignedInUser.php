<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Shared\UserId;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

/**
 * The user of the session, for the routes behind the `auth` middleware: the only place a controller
 * learns whose list it acts on. v1 took the user id from the URL and never checked it.
 */
final class SignedInUser
{
    /**
     * The signed-in user's id.
     *
     * @throws AuthenticationException when nobody is signed in (a route outside `auth`).
     */
    public static function id(Request $request): UserId
    {
        $id = $request->user()?->getAuthIdentifier();

        return is_int($id) ? new UserId($id) : throw new AuthenticationException;
    }
}
