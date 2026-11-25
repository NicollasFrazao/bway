<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The assortment of each store (v1's `setor_produto`): the sector a product is sold in.
 *
 * v1 let a product sit in several sectors of a store; the domain sells it in one
 * (docs/adr/0004-visit-planning.md), so (store, product) is unique. The sector is referenced together
 * with the store, so a row cannot place a product in another store's sector.
 */
return new class extends Migration
{
    /**
     * Creates the sector_products table.
     */
    public function up(): void
    {
        Schema::create('sector_products', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('sector_id');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['store_id', 'product_id']);
            $table->foreign(['store_id', 'sector_id'])
                ->references(['store_id', 'id'])
                ->on('sectors')
                ->cascadeOnDelete();
        });
    }

    /**
     * Drops the sector_products table.
     */
    public function down(): void
    {
        Schema::dropIfExists('sector_products');
    }
};
