<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use App\Domain\Store\Assortment;
use App\Domain\Store\AssortmentRepository;
use App\Domain\Store\StoreId;

/**
 * Serves a fixed set of assortments from memory.
 */
final readonly class InMemoryAssortmentRepository implements AssortmentRepository
{
    /**
     * The assortments, keyed by store id.
     *
     * @var array<int, Assortment>
     */
    private array $assortments;

    /**
     * Holds the assortments the tests need, at most one per store.
     */
    public function __construct(Assortment ...$assortments)
    {
        $keyed = [];

        foreach ($assortments as $assortment) {
            $keyed[$assortment->storeId->value] = $assortment;
        }

        $this->assortments = $keyed;
    }

    /**
     * The store's assortment, or an empty one.
     */
    public function forStore(StoreId $storeId): Assortment
    {
        return $this->assortments[$storeId->value] ?? new Assortment($storeId);
    }
}
