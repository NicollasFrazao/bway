<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The grid each store's floor plan was mapped to (v1's `ds_mapeamento` column and mapping files):
 * every cell code (0 to 4, as in v1), row after row, in one string. Store 1's grid is 478 x 492, about
 * 235 thousand characters, so the column is a MEDIUMTEXT (up to 16 MB).
 */
return new class extends Migration
{
    /**
     * Creates the store_grids table.
     */
    public function up(): void
    {
        Schema::create('store_grids', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->mediumText('cells');
            $table->timestamps();
        });
    }

    /**
     * Drops the store_grids table.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_grids');
    }
};
