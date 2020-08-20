<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbEstabelecimentoTable extends Migration
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
            'tb_estabelecimento', 
            function (Blueprint $table) 
            {
                $table -> increments('cd_estabelecimento') -> index();
                $table -> string('nm_estabelecimento', 100) -> index();
                $table -> string('ds_coordenadas', 100);
                $table -> float('vl_largura_metros', 8, 2);
                $table -> float('vl_comprimento_metros', 8, 2);
                $table -> integer('vl_largura_pixels') -> unsigned();
                $table -> integer('vl_comprimento_pixels') -> unsigned();
                $table -> json('ds_mapeamento');
                $table -> string('nm_email', 100) -> index() -> unique();
                $table -> string('cd_senha', 256);
                $table -> boolean('ic_confirmado') -> default(false);

                $table -> timestamps();
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
        Schema::dropIfExists('tb_estabelecimento');
    }
}
