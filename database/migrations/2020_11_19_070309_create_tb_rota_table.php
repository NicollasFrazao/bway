<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbRotaTable extends Migration
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
            'tb_rota', 
            function (Blueprint $table) 
            {
                $table -> integer('cd_estabelecimento') -> unsigned();
                $table -> integer('cd_origem') -> unsigned();
                $table -> integer('cd_destino') -> unsigned();

                $table -> timestamps();

                $table -> primary(['cd_estabelecimento', 'cd_origem', 'cd_destino']);

                $table -> foreign('cd_estabelecimento') 
                    -> references('cd_estabelecimento') 
                        -> on('tb_estabelecimento')
                    -> constrained()
                    -> onDelete('cascade') 
                    -> onUpdate('cascade');
                
                $table -> foreign('cd_origem') 
                    -> references('cd_setor') 
                        -> on('tb_setor')
                    -> constrained()
                    -> onDelete('cascade') 
                    -> onUpdate('cascade');
                
                $table -> foreign('cd_destino') 
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
        Schema::dropIfExists('tb_rota');
    }
}
