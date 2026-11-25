<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * The identity of a user: the id of an account and the owner of a shopping list.
 *
 * It lives in Shared because the shopping list needs only who owns it, not the account.
 */
final readonly class UserId extends Identifier {}
