<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The product catalogue (v1's `tb_produto`). A name is unique, as it was in v1, and at most 100
 * characters, the limit of the domain's Name.
 */
return new class extends Migration
{
    /**
     * Creates the products table.
     */
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });
    }

    /**
     * Drops the products table.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
