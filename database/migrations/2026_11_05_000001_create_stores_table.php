<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Stores and their layout (v1's `tb_estabelecimento`, `tb_setor` and `tb_divisoria`): sectors and
 * partitions are rectangles of the store's grid, by their top-left cell, width and height.
 *
 * The constraints repeat what the Store aggregate checks when it is loaded, so a row that would make
 * a store impossible to load is refused when it is written: at most one entrance per store (through
 * a generated column that holds the store id on the entrance only, under a unique index), and the
 * pair (store, sector) unique so the assortment and the routes can refer to a sector of the same
 * store.
 */
return new class extends Migration
{
    /**
     * Creates the stores, sectors and partitions tables.
     */
    public function up(): void
    {
        Schema::create('stores', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('sectors', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('kind', ['entrance', 'sales']);
            $table->unsignedInteger('x');
            $table->unsignedInteger('y');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedBigInteger('entrance_of_store_id')
                ->nullable()
                ->virtualAs("if(`kind` = 'entrance', `store_id`, null)")
                ->unique();
            $table->timestamps();
            $table->unique(['store_id', 'id']);
        });

        Schema::create('partitions', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('x');
            $table->unsignedInteger('y');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->timestamps();
        });
    }

    /**
     * Drops the layout tables, then the stores.
     */
    public function down(): void
    {
        Schema::dropIfExists('partitions');
        Schema::dropIfExists('sectors');
        Schema::dropIfExists('stores');
    }
};
