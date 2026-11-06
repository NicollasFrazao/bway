<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Shopping lists (v1's `tb_lista_compras`): one list per user, and its items in the order they were
 * added. An item keeps the name its product had when it was added (docs/adr/0002-domain-model.md).
 * (list, product) is unique, as the aggregate refuses a duplicate item.
 */
return new class extends Migration
{
    /**
     * Creates the shopping_lists and shopping_list_items tables.
     */
    public function up(): void
    {
        Schema::create('shopping_lists', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('shopping_list_items', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name', 100);
            $table->timestamps();
            $table->unique(['shopping_list_id', 'product_id']);
            $table->unique(['shopping_list_id', 'position']);
        });
    }

    /**
     * Drops the items, then the lists.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_list_items');
        Schema::dropIfExists('shopping_lists');
    }
};
