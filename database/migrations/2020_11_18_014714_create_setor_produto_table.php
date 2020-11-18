<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetorProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create
        (
            'setor_produto', 
            function (Blueprint $table) 
            {
                $table -> integer('cd_setor') -> unsigned();
                $table -> integer('cd_produto') -> unsigned();

                $table -> timestamps();

                $table -> primary(['cd_setor', 'cd_produto']);

                $table -> foreign('cd_setor') 
                    -> references('cd_setor') 
                        -> on('tb_setor')
                    -> constrained()
                    -> onDelete('cascade') 
                    -> onUpdate('cascade');
                
                $table -> foreign('cd_produto') 
                    -> references('cd_produto') 
                        -> on('tb_produto')
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
        Schema::dropIfExists('setor_produto');
    }
}
