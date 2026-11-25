<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The ordered pairs of sectors of a store that no walkway joins, found while generating its routes
     * (B7), so store management shows which sectors cannot be reached.
     */
    public function up(): void
    {
        Schema::create('unreachable_routes', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('from_sector_id');
            $table->unsignedBigInteger('to_sector_id');
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
     * Drops the table.
     */
    public function down(): void
    {
        Schema::dropIfExists('unreachable_routes');
    }
};
