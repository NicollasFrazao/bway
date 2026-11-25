<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;

/**
 * Reads the name a person signs up with: any {@see Name}, refused as an account field so the form
 * shows the error on the name.
 */
final class AccountName
{
    /**
     * The person's name.
     *
     * @throws InvalidAccountData when the name is blank, too long or not valid UTF-8.
     */
    public static function from(string $text): Name
    {
        try {
            return Name::from($text);
        } catch (InvalidName $invalid) {
            throw InvalidAccountData::of(AccountField::Name, $invalid->getMessage());
        }
    }
}
