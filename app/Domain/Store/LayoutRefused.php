<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\DomainError;

/**
 * A store's layout breaks a rule of the Store aggregate: parts that overlap or leave the grid, no
 * entrance or several, two parts with one id. Store management turns any of them into one message on
 * the form.
 */
abstract class LayoutRefused extends DomainError {}
