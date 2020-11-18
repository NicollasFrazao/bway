<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarrinhoProdutoTable extends Migration
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
            'carrinho_produto', 
            function (Blueprint $table) 
            {
                $table -> integer('cd_carrinho') -> unsigned();
                $table -> integer('cd_produto') -> unsigned();

                $table -> boolean('ic_adquirido') -> default(false);

                $table -> timestamps();

                $table -> primary(['cd_carrinho', 'cd_produto']);

                $table -> foreign('cd_carrinho') 
                    -> references('cd_carrinho') 
                        -> on('tb_carrinho')
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
        Schema::dropIfExists('carrinho_produto');
    }
}
