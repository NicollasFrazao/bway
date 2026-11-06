<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * The identity of a user, the owner of a shopping list.
 *
 * Accounts themselves are modelled with authorisation (B5); until then the domain needs only who
 * owns what.
 */
final readonly class UserId extends Identifier {}
