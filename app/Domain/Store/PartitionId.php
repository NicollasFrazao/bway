<?php

declare(strict_types=1);

namespace App\Domain\Store;

use App\Domain\Shared\Identifier;

/**
 * The identity of a partition of a store.
 */
final readonly class PartitionId extends Identifier {}
