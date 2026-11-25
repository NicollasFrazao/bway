<?php

declare(strict_types=1);

namespace App\Application\Store;

use App\Domain\Catalog\Product;
use App\Domain\Store\Assortment;
use App\Domain\Store\Store;

/**
 * What the assortment form shows: the store with its sectors, every product, and where each is sold.
 */
final readonly class AssortmentView
{
    /**
     * Describes the form.
     *
     * @param  list<Product>  $products  Every product of the catalogue, by name.
     */
    public function __construct(
        public Store $store,
        public array $products,
        public Assortment $assortment,
    ) {}
}
