<?php

declare(strict_types=1);

namespace App\Domain\Grid;

use App\Domain\Shared\DomainError;

/**
 * A rectangle or a cell typed for the grid cannot exist: a negative coordinate, or no width or height.
 */
abstract class InvalidGridArea extends DomainError {}
