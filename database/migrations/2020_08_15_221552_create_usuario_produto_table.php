<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioProdutoTable extends Migration
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
            'usuario_produto', 
            function (Blueprint $table) 
            {
                $table -> integer('cd_usuario') -> unsigned();
                $table -> integer('cd_produto') -> unsigned();

                $table -> timestamps();

                $table -> primary(['cd_usuario', 'cd_produto']);

                $table -> foreign('cd_usuario') 
                    -> references('cd_usuario') 
                        -> on('tb_usuario')
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
        Schema::dropIfExists('usuario_produto');
    }
}
