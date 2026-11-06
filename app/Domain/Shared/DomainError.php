<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use RuntimeException;

/**
 * A rule of the domain was broken; the operation that broke it changed nothing.
 *
 * Every domain exception extends this class, so the outer layers can turn any of them into one
 * kind of answer (a validation message, a 4xx response) without listing them one by one.
 */
abstract class DomainError extends RuntimeException {}
