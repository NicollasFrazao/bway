<?php

declare(strict_types=1);

namespace App\Domain\Account;

/**
 * The values of an account a person types, named as the forms name them.
 */
enum AccountField: string
{
    case Name = 'name';
    case Email = 'email';
    case Password = 'password';
}
