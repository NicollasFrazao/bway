<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The route cache (v1's `tb_rota` and route files): the route found between an ordered pair of
 * sectors of a store, as a JSON list of [x, y] cells from the start to the target. Both sectors are
 * referenced together with the store, so a route cannot join sectors of another store, and deleting
 * a sector deletes its routes.
 */
return new class extends Migration
{
    /**
     * Creates the routes table.
     */
    public function up(): void
    {
        Schema::create('routes', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('from_sector_id');
            $table->unsignedBigInteger('to_sector_id');
            $table->json('cells');
            $table->timestamps();
            $table->unique(['store_id', 'from_sector_id', 'to_sector_id']);
            $table->foreign(['store_id', 'from_sector_id'])
                ->references(['store_id', 'id'])
                ->on('sectors')
                ->cascadeOnDelete();
            $table->foreign(['store_id', 'to_sector_id'])
                ->references(['store_id', 'id'])
                ->on('sectors')
                ->cascadeOnDelete();
        });
    }

    /**
     * Drops the routes table.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
