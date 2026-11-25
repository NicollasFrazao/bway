<?php

declare(strict_types=1);

namespace App\Domain\Account;

/**
 * What an account may do: every account shops; an administrator also manages the stores.
 */
enum Role: string
{
    case Shopper = 'shopper';
    case Administrator = 'administrator';
}
