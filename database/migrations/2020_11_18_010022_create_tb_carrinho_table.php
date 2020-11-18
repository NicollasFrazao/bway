<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbCarrinhoTable extends Migration
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
            'tb_carrinho', 
            function (Blueprint $table) 
            {
                //$table -> integer('cd_carrinho') -> unique() -> index();

                $table -> integer('cd_usuario') -> unsigned();
                $table -> integer('cd_estabelecimento') -> unsigned();

                $table -> timestamps();

                $table -> primary(['cd_usuario', 'cd_estabelecimento']);

                $table -> foreign('cd_usuario') 
                    -> references('cd_usuario') 
                        -> on('tb_usuario')
                    -> constrained()
                    -> onDelete('cascade') 
                    -> onUpdate('cascade');
                
                $table -> foreign('cd_estabelecimento') 
                    -> references('cd_estabelecimento') 
                        -> on('tb_estabelecimento')
                    -> constrained()
                    -> onDelete('cascade') 
                    -> onUpdate('cascade');
            }
        );

        DB::statement('alter table tb_carrinho add cd_carrinho integer unsigned not null unique auto_increment;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_carrinho');
    }
}
