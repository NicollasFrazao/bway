<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCarrinhoProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table
        (
            'carrinho_produto', 
            function (Blueprint $table) 
            {
                $table -> integer('cd_setor') -> unsigned() -> nullable() -> after('cd_produto');

                $table -> foreign('cd_setor') 
                    -> references('cd_setor') 
                        -> on('tb_setor')
                    -> constrained()
                    -> onDelete('cascade') 
                    -> onUpdate('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
